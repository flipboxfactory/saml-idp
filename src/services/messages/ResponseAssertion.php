<?php

namespace flipbox\saml\idp\services\messages;


use craft\base\Component;
use craft\elements\User;
use craft\helpers\ConfigHelper;
use flipbox\keychain\KeyChain;
use flipbox\keychain\records\KeyChainRecord;
use flipbox\saml\core\helpers\ProviderHelper;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\Saml;
use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Assertion\Attribute;
use LightSaml\Model\Assertion\AttributeStatement;
use LightSaml\Model\Assertion\AuthnContext;
use LightSaml\Model\Assertion\AuthnStatement;
use LightSaml\Model\Assertion\Conditions;
use LightSaml\Model\Assertion\EncryptedAssertionWriter;
use LightSaml\Model\Assertion\NameID;
use LightSaml\Model\Assertion\Subject;
use LightSaml\Model\Assertion\SubjectConfirmation;
use LightSaml\Model\Assertion\SubjectConfirmationData;
use LightSaml\Model\Protocol\AuthnRequest;
use LightSaml\Model\Protocol\Response as ResponseMessage;
use LightSaml\Model\XmlDSig\SignatureWriter;
use LightSaml\SamlConstants;

class ResponseAssertion extends Component
{
    /**
     * @param AuthnRequest $authnRequest
     * @param ResponseMessage $response
     * @param ProviderRecord $spProvider
     * @param ProviderRecord $idpProvider
     * @return Assertion
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function create(
        AuthnRequest $authnRequest,
        ResponseMessage $response,
        ProviderRecord $spProvider,
        ProviderRecord $idpProvider
    )
    {
        /**
         * Get User
         */
        $user = $this->getUser();

        /**
         * Add Assertion
         */
        $response->addAssertion(
            $assertion = new Assertion()
        );

        /**
         * Reuse the set issuer
         */
        $assertion->setIssuer(
            $response->getIssuer()
        );

        /**
         * Subject
         */
        $assertion->setSubject(
            $this->createSubject($authnRequest, $user)
        );

        /**
         * Conditions
         */
        $assertion->setConditions(
            $this->createConditions()
        );

        /**
         * AuthnStatement
         */
        $assertion->addItem(
            $this->createAuthnStatement()
        );

        /**
         * Attributes
         */
        $assertion->addItem(
            $this->createAttributeStatement(
                $user
            )
        );

        /**
         * Sign Assertions
         */
        if ($spProvider->getMetadataModel()->getFirstSpSsoDescriptor()->getWantAssertionsSigned()) {
            $assertion->setSignature(
                new SignatureWriter(
                    (new X509Certificate())->loadPem(
                        $idpProvider->getKeychain()->one()->getDecryptedCertificate()
                    ),
                    KeyHelper::createPrivateKey(
                        $idpProvider->getKeychain()->one()->getDecryptedKey(),
                        ''
                    )
                )
            );
        }

        /**
         * Encrypt Assertions
         */

        if (Saml::getInstance()->getSettings()->encryptAssertions) {
            $response->addEncryptedAssertion(
                $this->createEncryptAssertion(
                    $assertion,
                    $spProvider
                )
            );
        } else {
            //default
            $response->addAssertion($assertion);
        }

        return $assertion;
    }

    /**
     * @param AuthnRequest $authnRequest
     * @param User $user
     * @return Subject
     * @throws \Exception
     */
    protected function createSubject(AuthnRequest $authnRequest, User $user)
    {
        $subject = new Subject();
        /**
         * Subject Confirmation
         * Reference: https://stackoverflow.com/a/29546696/1590910
         *
         * The times in the <SubjectConfirmationData> signals for how long time assertion can be tied to the subject.
         * In Web SSO where the subject confirmation method "bearer" is usually used, it means that within this time
         * we can trust that the assertion applies to the one providing the assertion. The assertion might be valid
         * for a longer time, but we must create a session within this time frame. This is described in the Web SSO
         * Profile section 4.1.4.3. The times in <SubjectConfirmationData> must fall within the interval of
         * those in <Conditions>.
         */

        /**
         * Add Subject Confirmation
         */
        $subject->addSubjectConfirmation(
            $subjectConfirmation = new SubjectConfirmation()
        );

        $subjectConfirmation->setMethod(
            \LightSaml\SamlConstants::CONFIRMATION_METHOD_BEARER
        );

        /**
         * Add Subject Confirmation Data
         */
        $subjectConfirmation->setSubjectConfirmationData(
            $subjectConfirmationData = new SubjectConfirmationData()
        );

        $subjectConfirmationData->setInResponseTo($authnRequest->getID())
            ->setNotOnOrAfter(
                new \DateTime(
                    Saml::getInstance()->getSettings()->messageNotOnOrAfter
                )
            )->setRecipient(
                $authnRequest->getAssertionConsumerServiceURL()
            );

        $subject->add
        $subject->setNameID(
            new NameID(
                $user->username,
                SamlConstants::NAME_ID_FORMAT_UNSPECIFIED
            )
        );

        return $subject;
    }

    /**
     * @return Conditions
     * @throws \Exception
     */
    protected function createConditions()
    {
        /**
         * Conditions
         * Reference: https://stackoverflow.com/a/29546696/1590910
         *
         * The times in <Conditions> is the validity of the entire assertion.
         * It should not be consumed after this time. There is nothing preventing a user
         * session on an SP to extend beyond this point in time though.
         */

        $conditions = new Conditions();

        $conditions->setNotBefore(
            new \DateTime(
                Saml::getInstance()->getSettings()->messageNotBefore
            )
        );

        $conditions->setNotOnOrAfter(
            (new \DateTime(
                Saml::getInstance()->getSettings()->messageNotOnOrAfter
            ))->getTimestamp()
        );

        return $conditions;
    }

    /**
     * @return AuthnStatement
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    protected function createAuthnStatement()
    {

        /**
         * Add AuthnStatement
         */
        $authnStatement = new AuthnStatement();


        /**
         * Reference: https://stackoverflow.com/a/29546696/1590910
         *
         * SessionNotOnOrAfter is something completely different that is not directly related to the lifetime of
         * the assertion or the subject. It is a parameter the idp can use to control how long an SP session may be.
         * Please note that this parameter is defined that it SHOULD be handled by an SP according to the SAML2Core
         * spec, but far from all SP implementations do. An example of an implementation that does is as usual
         * Shibboleth, that always will respect the occurence of this parameter. When using Single Logout, this
         * parameter is more critical, as it synchronizes the timeout of the session on both the SP and the
         * Idp, to ensure that an SP does not issue a logout request for a session no longer known to the Idp.
         */
        $sessionEnd = (new \DateTime())->setTimestamp(
            ConfigHelper::durationInSeconds(
            /**
             * Use crafts user session duration
             */
                \Craft::$app->config->getGeneral()->userSessionDuration
            )
            + // Math!
            (new \DateTime())->getTimestamp()
        );

        /**
         * Add AuthnStatement attributes and AuthnContext
         */
        $authnStatement->setAuthnInstant(new \DateTime())
            ->setSessionNotOnOrAfter(
                $sessionEnd
            )->setSessionIndex(
            /**
             * Just mask the session id
             */
                \Craft::$app->security->hashData(
                    \Craft::$app->session->getId()
                )
            )->setAuthnContext(
                $authnContext = new AuthnContext()
            );
        $authnContext->setAuthnContextClassRef(
            SamlConstants::AUTHN_CONTEXT_PASSWORD
        );

        return $authnStatement;
    }

    protected function createAttributeStatement(
        User $user
    )
    {

        $attributeStatement = new AttributeStatement();

        /**
         * Check the provider first
         */
        $attributeMap =
//            ProviderHelper::providerMappingToKeyValue(
//                $idpProvider = Saml::getInstance()->getProvider()->findByEntityId(
//                    $response->getIssuer()->getValue()
//                )->one()
//            ) ?:
            Saml::getInstance()->getSettings()->responseAttributeMap;

        foreach ($attributeMap as $craftProperty => $attributeName) {

            $this->assignProperty(
                $user,
                $attributeStatement,
                $attributeName,
                $craftProperty
            );

        }

        return $attributeStatement;
    }

    /**
     * @param Assertion $assertion
     * @param ProviderRecord $spProvider
     * @return EncryptedAssertionWriter
     */
    protected function createEncryptAssertion(
        Assertion $assertion,
        ProviderRecord $spProvider
    )
    {
        /** @var KeyChainRecord $keypair */
        $keypair = $spProvider->getKeychain()->one();

        $certificate = (new X509Certificate())->setData(
            $keypair->getDecryptedCertificate()
        );
        $encryptedAssertion = new EncryptedAssertionWriter();
        $encryptedAssertion->encrypt(
            $assertion,
            KeyHelper::createPublicKey(
                $certificate
            )
        );

        return $encryptedAssertion;
    }

    /**
     * Utilities
     */

    /**
     * @param User $user
     * @param Assertion $assertion
     * @param $attributeName
     * @param $craftProperty
     * @return AttributeStatement
     */
    protected function assignProperty(
        User $user,
        AttributeStatement $attributeStatement,
        $attributeName,
        $craftProperty
    )
    {
        $attribute = $attributeStatement->addAttribute(
            new Attribute(
                $attributeName,
                $user->{$craftProperty}
            )
        );

        return $attribute;

    }

    /**
     * @return User|false|\yii\web\IdentityInterface|null
     */
    protected function getUser()
    {
        return \Craft::$app->getUser()->getIdentity();
    }
}
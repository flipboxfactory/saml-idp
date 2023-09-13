<?php

namespace flipbox\saml\idp\services\messages;

use craft\base\Component;
use craft\elements\User;
use craft\helpers\ConfigHelper;
use flipbox\saml\core\models\AttributeMap;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\idp\models\Settings;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\Saml;
use SAML2\Assertion;
use SAML2\AuthnRequest as SamlAuthnRequest;
use SAML2\Constants;
use SAML2\EncryptedAssertion;
use SAML2\Response as ResponseMessage;
use SAML2\XML\saml\NameID;
use SAML2\XML\saml\SubjectConfirmation;
use SAML2\XML\saml\SubjectConfirmationData;

class ResponseAssertion extends Component
{
    public function create(
        User $user,
        ResponseMessage $response,
        ProviderRecord $identityProvider,
        ProviderRecord $serviceProvider,
        Settings $settings,
        SamlAuthnRequest $authnRequest = null
    ) {
        $assertion = new Assertion();

        $issuer = $response->getIssuer();
        if (! is_null($issuer)) {
            $assertion->setIssuer(
                $issuer
            );
        }


        $assertion->setSubjectConfirmation([
            $this->createSubjectConfirmation(
                $serviceProvider,
                $user,
                $settings,
                $authnRequest
            ),
        ]);

        $urlParts = parse_url(
            $response->getDestination()
        );

        if (isset($urlParts['scheme']) && isset($urlParts['host'])) {
            // allow all
            $assertion->setValidAudiences([
                $serviceProvider->getEntityId(),
            ]);
        }

        $this->createConditions($assertion, $settings);

        $this->createAuthnStatement($assertion);

        $this->setAssertionAttributes(
            $user,
            $assertion,
            $serviceProvider,
            $settings
        );

        $firstDescriptor = $serviceProvider->spSsoDescriptors()[0];

        // Sign Assertions
        if ($firstDescriptor->wantAssertionsSigned()) {
            $assertion->setCertificates(
                [
                    $identityProvider->keychain->getDecryptedCertificate(),
                ]
            );
            $assertion->setSignatureKey(
                $identityProvider->keychainPrivateXmlSecurityKey()
            );
        }


        // Encrypt Assertions
        if ($serviceProvider->encryptAssertions) {
            $unencrypted = $assertion;

            if (is_null($serviceProvider->encryptionKey())) {
                throw new \Exception('No encryption key found for the service provider.');
            }
            $unencrypted->setEncryptionKey(
                $serviceProvider->encryptionKey()
            );

            $assertion = new EncryptedAssertion();
            $assertion->setAssertion(
                $unencrypted,
                $serviceProvider->encryptionKey()
            );
        }

        $response->setAssertions(
            [
                $assertion,
            ]
        );

        return $assertion;
    }

    /**
     * @param AbstractProvider $serviceProvider
     * @param User $user
     * @param Settings $settings
     * @param SamlAuthnRequest|null $authnRequest
     * @return SubjectConfirmation
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    protected function createSubjectConfirmation(
        AbstractProvider $serviceProvider,
        User $user,
        Settings $settings,
        SamlAuthnRequest $authnRequest = null
    ) {
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

        $subjectConfirmation = new SubjectConfirmation();

        $subjectConfirmation->setMethod(
            Constants::CM_BEARER
        );


        // Add Subject Confirmation Data
        $subjectConfirmation->setSubjectConfirmationData(
            $subjectConfirmationData = new SubjectConfirmationData()
        );

        $subjectConfirmationData->setNotOnOrAfter(
            (new \DateTime(
                $settings->messageNotOnOrAfter
            ))->getTimestamp()
        );

        if ($authnRequest) {
            $subjectConfirmationData->setInResponseTo($authnRequest->getId());
        }

        $subjectConfirmationData->setRecipient(
            $authnRequest ? $authnRequest->getAssertionConsumerServiceURL() : $serviceProvider->firstSpAcsService()->getLocation()
        );

        $subjectConfirmation->setNameID(
            $nameId = new NameID()
        );

        $nameId->setFormat(Constants::NAMEID_UNSPECIFIED);
        $nameId->setNameQualifier(
            $settings->getEntityId()
        );
        $nameId->setValue(
            $serviceProvider->assignNameId($user)
        );

        return $subjectConfirmation;
    }

    /**
     * @param Assertion $assertion
     * @throws \Exception
     */
    protected function createConditions(
        Assertion $assertion,
        Settings $settings
    ) {
        /**
         * Conditions
         * Reference: https://stackoverflow.com/a/29546696/1590910
         *
         * The times in <Conditions> is the validity of the entire assertion.
         * It should not be consumed after this time. There is nothing preventing a user
         * session on an SP to extend beyond this point in time though.
         */

        $assertion->setNotBefore(
            (new \DateTime(
                $settings->messageNotBefore
            ))->getTimestamp()
        );

        $assertion->setNotOnOrAfter(
            (new \DateTime(
                $settings->messageNotOnOrAfter
            ))->getTimestamp()
        );
    }

    /**
     * @param Assertion $assertion
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    protected function createAuthnStatement(Assertion $assertion)
    {
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
        $assertion->setAuthnInstant((new \DateTime())->getTimestamp());
        $assertion->setSessionNotOnOrAfter(
            $sessionEnd->getTimestamp()
        );

        $assertion->setSessionIndex(
            Saml::getInstance()->getSession()->getId()
        );

        $assertion->setAuthnContextClassRef(
            Constants::AC_PASSWORD_PROTECTED_TRANSPORT
        );
    }

    public function setAssertionAttributes(
        User $user,
        Assertion $assertion,
        ProviderRecord $serviceProvider,
        Settings $settings
    ) {

        // set on the assertion and the subject confirmations
        $assertion->setNameID(
            $nameId = new NameID()
        );

        $nameId->setFormat(Constants::NAMEID_UNSPECIFIED);
        $nameId->setValue(
            $serviceProvider->assignNameId($user)
        );


        // Check the provider first
        $attributeMap =
            $serviceProvider->hasMapping() ? $serviceProvider->getMapping() : $settings->responseAttributeMap;

        $attributes = [];
        foreach ($attributeMap as $map) {
            $map = new AttributeMap($map);
            $attributes[$map->attributeName] = $this->assignProperty(
                $user,
                $map
            );
        }

        // Add groups if configured
        if ($serviceProvider->syncGroups &&
            ($groupAttribute = $this->groupsToAttributes($user, $serviceProvider))
        ) {
            $attributes[$serviceProvider->groupsAttributeName] = $groupAttribute;
        }

        Saml::debug(json_encode($attributes));
        $assertion->setAttributes($attributes);
    }

    /**
     * @param User $user
     * @param AbstractProvider $serviceProvider
     * @return array|bool
     */
    protected function groupsToAttributes(User $user, AbstractProvider $serviceProvider)
    {
        if (count($user->getGroups()) === 0) {
            return false;
        }
        $attribute = [];
        foreach ($user->getGroups() as $group) {
            $options = $serviceProvider->getGroupOptions();
            if (! $options->shouldSync($group->id)) {
                continue;
            }

            $attribute[] = $group->handle;
        }

        return $attribute;
    }

    /**
     * Utilities
     */

    /**
     * @param User $user
     * @param $attributeName
     * @param $craftProperty
     * @return array
     */
    protected function assignProperty(
        User $user,
        AttributeMap $map
    ) {
        $value = $map->renderValue($user);

        if ($value instanceof \DateTime) {
            $value = $value->format(\DateTime::ISO8601);
        }

        return [
            $map->attributeName => $value,
        ];
    }

    /**
     * @return User|false|\yii\web\IdentityInterface|null
     */
    protected function getUser()
    {
        return \Craft::$app->getUser()->getIdentity();
    }
}

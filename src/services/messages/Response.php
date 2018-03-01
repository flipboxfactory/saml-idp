<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/11/18
 * Time: 8:30 PM
 */

namespace flipbox\saml\idp\services\messages;


use craft\base\Component;
use craft\helpers\ConfigHelper;
use flipbox\saml\core\events\RegisterAttributesTransformer;
use flipbox\saml\core\exceptions\InvalidMessage;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\core\transformers\AbstractResponseToUser;
use flipbox\saml\idp\records\ProviderRecord as Provider;
use flipbox\saml\idp\Saml;
use flipbox\saml\idp\transformers\ResponseAssertion;
use Flipbox\Transform\Factory;
use flipbox\saml\idp\services\bindings\Factory as BindingFactory;
use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Helper;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Assertion\AttributeStatement;
use LightSaml\Model\Assertion\AuthnContext;
use LightSaml\Model\Assertion\AuthnStatement;
use LightSaml\Model\Assertion\Conditions;
use LightSaml\Model\Assertion\Issuer;
use LightSaml\Model\Assertion\NameID;
use LightSaml\Model\Assertion\Subject;
use LightSaml\Model\Assertion\SubjectConfirmation;
use LightSaml\Model\Assertion\SubjectConfirmationData;
use LightSaml\Model\Protocol\AuthnRequest;
use LightSaml\Model\Protocol\Response as ResponseMessage;
use LightSaml\Model\Protocol\Status;
use LightSaml\Model\Protocol\StatusCode;
use LightSaml\Model\XmlDSig\SignatureWriter;
use LightSaml\SamlConstants;

class Response extends Component
{

    const CONSENT_IMPLICIT = 'urn:oasis:names:tc:SAML:2.0:consent:current-implicit';
    const EVENT_ASSERTION_TRANSFORMATION = 'atAssertionTransformation';
    const DEFAULT_ATTRIBUTE_TRANSFORMER = ResponseAssertion::class;

    public function create(AuthnRequest $authnRequest, $relayState = null)
    {

        /** @var Provider $idpProvider */
        $idpProvider = Saml::getInstance()->getProvider()->findOwn();
        $spProvider = Saml::getInstance()->getProvider()->findByEntityId(
            $authnRequest->getIssuer()->getValue()
        );

        $response = new ResponseMessage();
        $response->setIssuer(
            new Issuer($idpProvider->entityId)
        );
        $response->setID(Helper::generateID())
            ->setDestination(
                $authnRequest->getAssertionConsumerServiceURL()
            )->setConsent(static::CONSENT_IMPLICIT)
            ->setInResponseTo(
                $authnRequest->getID()
            )->setStatus(
                new Status(
                    new StatusCode(SamlConstants::STATUS_SUCCESS
                    )
                )
            )->setVersion(SamlConstants::VERSION_20)
            ->setIssueInstant(
                new \DateTime()
            );


        if ($relayState) {
            $response->setRelayState($relayState);
        }

        /**
         * Get User
         */
        $user = \Craft::$app->getUser()->getIdentity();

        /**
         * Add Assertion
         */
        $response->addAssertion(
            $assertion = new Assertion()
        );

        $assertion->setIssuer(
            $response->getIssuer()
        );

        $assertion->addItem(
            $attributeStatement = new AttributeStatement()
        );
        /**
         * Add Assertion Subject
         */
        $assertion->setSubject(
            $subject = new Subject()
        );

        $subject->setNameID(
            new NameID(
                $user->username,
                SamlConstants::NAME_ID_FORMAT_UNSPECIFIED
            )
        );


        $subject->addSubjectConfirmation(
            $subjectConfirmation = new SubjectConfirmation()
        );

        $subjectConfirmation->setMethod(
            \LightSaml\SamlConstants::CONFIRMATION_METHOD_BEARER
        );

        $subjectConfirmation->setSubjectConfirmationData(
            $subjectConfirmationData = new SubjectConfirmationData()
        );

        $subjectConfirmationData->setInResponseTo($authnRequest->getID())
            ->setNotOnOrAfter(
                new \DateTime('+1 MINUTE')
            )->setRecipient(
                $authnRequest->getAssertionConsumerServiceURL()
            );

        /**
         * Conditions
         */

        $assertion->setConditions(
            $conditions = new Conditions()
        );

        $conditions->setNotBefore(
            (new \DateTime())
        );

        $conditions->setNotOnOrAfter(
            new \DateTime('+1 MINUTE')
        );

        $assertion->addItem(
            $authnStatement = new AuthnStatement()
        );

        $sessionEnd = (new \DateTime())->setTimestamp(ConfigHelper::durationInSeconds(
                \Craft::$app->config->getGeneral()->userSessionDuration
            ) + (new \DateTime())->getTimestamp());

        $authnStatement->setAuthnInstant(new \DateTime())
            ->setSessionNotOnOrAfter(
                $sessionEnd
            )->setSessionIndex(
                \Craft::$app->session->getId()
            )->setAuthnContext(
                $autnContext = new AuthnContext()
            );
        $autnContext->setAuthnContextClassRef(
            SamlConstants::AUTHN_CONTEXT_PASSWORD
        );

        $transformer = $this->getTransformer($spProvider);

        Factory::item(new $transformer($user), $assertion);

        $response->setSignature(
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


//        if ($spProvider->encryptAssertions) {
//            $this->encryptAssertion(
//                $response->getFirstAssertion()
//            );
//        }

        /**
         * Sign message?
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

        return $response;
    }


    public function getTransformer(Provider $providerRecord)
    {
        /**
         * Transformation Event
         */
        $event = new RegisterAttributesTransformer();

        $this->trigger(
            static::EVENT_ASSERTION_TRANSFORMATION,
            $event
        );

        $transformer = $event->getTransformer($providerRecord->getEntityId());
        if (! $transformer) {
            $transformer = static::DEFAULT_ATTRIBUTE_TRANSFORMER;
        }
        return $transformer;

    }

    /**
     * @param AuthnRequest $authnRequest
     * @throws \flipbox\saml\core\exceptions\InvalidMetadata
     */
    public function createAndSend(AuthnRequest $authnRequest)
    {
        $response = $this->create($authnRequest);
        $provider = Saml::getInstance()->getProvider()->findByEntityId(
            $authnRequest->getIssuer()->getValue()
        );
        $this->send($response, $provider);
    }

    /**
     * @throws InvalidMessage
     * @throws \flipbox\saml\core\exceptions\InvalidMetadata
     */
    public function createAndSendFromSession()
    {
        $authnRequest = Saml::getInstance()->getSession()->getAuthnRequest();

        $response = $this->create($authnRequest, Saml::getInstance()->getSession()->getRelayState());

        $provider = Saml::getInstance()->getProvider()->findByEntityId(
            $authnRequest->getIssuer()->getValue()
        );

        $this->send($response, $provider);
    }

    /**
     * @param ResponseMessage $response
     * @param ProviderInterface $provider
     * @throws \flipbox\saml\core\exceptions\InvalidMetadata
     */
    public function send(\LightSaml\Model\Protocol\Response $response, ProviderInterface $provider)
    {
        BindingFactory::send($response, $provider);
        exit(__METHOD__);
    }
}
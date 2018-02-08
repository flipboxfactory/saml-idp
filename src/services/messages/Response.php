<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/11/18
 * Time: 8:30 PM
 */

namespace flipbox\saml\idp\services\messages;


use craft\base\Component;
use craft\elements\User;
use flipbox\saml\core\events\RegisterAttributesTransformer;
use flipbox\saml\core\exceptions\InvalidMessage;
use flipbox\saml\core\services\traits\Security;
use flipbox\saml\core\transformers\AbstractResponseToUser;
use flipbox\saml\idp\models\Provider;
use flipbox\saml\idp\Saml;
use flipbox\saml\idp\services\bindings\HttpPost;
use Flipbox\Transform\Factory;
use LightSaml\Action\Profile\Inbound\StatusResponse\StatusAction;
use LightSaml\Credential\X509Certificate;
use LightSaml\Helper;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Assertion\Attribute;
use LightSaml\Model\Assertion\AttributeStatement;
use LightSaml\Model\Protocol\AuthnRequest;
use LightSaml\Model\Protocol\Response as ResponseMessage;
use LightSaml\Model\Protocol\SamlMessage;
use LightSaml\Model\Protocol\Status;
use LightSaml\Model\Protocol\StatusCode;
use LightSaml\Model\XmlDSig\SignatureWriter;
use LightSaml\SamlConstants;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class Response extends Component
{

    use Security;

    const CONSENT_IMPLICIT = 'urn:oasis:names:tc:SAML:2.0:consent:current-implicit';
    const EVENT_ASSERTION_TRANSFORMATION = 'atAssertionTransformation';

    /**
     * @return XMLSecurityKey
     */
    public function getKey(): XMLSecurityKey
    {
        return \LightSaml\Credential\KeyHelper::createPrivateKey(
            Saml::getInstance()->getSettings()->keyPath,
            '',
            true
        );
    }

    /**
     * @return X509Certificate
     */
    public function getCertificate(): X509Certificate
    {
        return X509Certificate::fromFile(
            Saml::getInstance()->getSettings()->certPath
        );
    }

    public function send(ResponseMessage $response, Provider $provider)
    {

        switch($provider->getMetadata()->getFirstSpSsoDescriptor()->getFirstAssertionConsumerService()->getBinding()){
            case SamlConstants::BINDING_SAML2_HTTP_POST:
                Saml::getInstance()->getHttpPost();
                break;
            case SamlConstants::BINDING_SAML2_HTTP_REDIRECT:
                break;
        }
    }

    public function createAndSend(AuthnRequest $authnRequest, string $relayState = null)
    {

        /** @var Provider $provider */
        if ($provider = Saml::getInstance()->getProvider()->findByIssuer($authnRequest->getIssuer())) {
            throw new InvalidMessage('invalid message');
        }

        $response = new ResponseMessage();
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
         * Add Assertion
         */
        $response->addAssertion(
            new Assertion()
        );

        /**
         * Get User
         */
        $user = \Craft::$app->getUser()->getIdentity();

        /**
         * Transformation Event
         */
        $event = new RegisterAttributesTransformer();
        $this->trigger(
            static::EVENT_ASSERTION_TRANSFORMATION,
            $event
        );

        /** @var AbstractResponseToUser $transformer */
        $transformer = $event->getTransformer($provider->getEntityId());

        /**
         * The transformer takes precedent due to it being more flexible and elegant
         */
        if ($transformer instanceof AbstractResponseToUser) {
            Factory::item(new $transformer($user), $response);
        } else {
            $this->setAttributesFromAssertion($user, $response->getFirstAssertion());
        }

        if ($provider->encryptAssertions) {
            $this->encryptAssertion(
                $response->getFirstAssertion()
            );
        }

        /**
         * Sign message?
         */
        if ($provider->getMetadata()->getFirstSpSsoDescriptor()->getWantAssertionsSigned()) {
            $this->signMessage($response);
        }


        return $this->send($response, $provider);
    }

    /**
     * @param User $user
     * @param Assertion $assertion
     */
    protected function setAttributesFromAssertion(User $user, Assertion $assertion)
    {
        $attributeMap = Saml::getInstance()->getSettings()->responseAttributeMap;
        $attributeStatement = new AttributeStatement();
        $assertion->addItem(
            $attributeStatement
        );

        foreach ($attributeMap as $samlAttribute => $craftProperty) {
            if ($user->{$craftProperty}) {
                $attributeStatement->addAttribute(
                    new Attribute(
                        $samlAttribute,
                        (string)$user->{$craftProperty}
                    )
                );
            }
        }

        return $assertion;
    }

    public function createAndSendFromSession()
    {

        if (! (($authnRequest = Saml::getInstance()->authnRequest()->getAuthnRequest()) instanceof AuthnRequest)) {
            throw new InvalidMessage("Invalid Message.");
        }

        return $this->createAndSend($authnRequest);
    }
}
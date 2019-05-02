<?php

namespace flipbox\saml\idp\services\messages;


use craft\base\Component;
use craft\elements\User;
use flipbox\saml\core\helpers\MessageHelper;
use flipbox\saml\core\services\bindings\Factory;
use flipbox\saml\idp\models\Settings;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\records\ProviderRecord as Provider;
use flipbox\saml\idp\Saml;
use SAML2\AuthnRequest;
use SAML2\Constants;
use SAML2\Response as ResponseMessage;

class Response extends Component
{

    const CONSENT_IMPLICIT = 'urn:oasis:names:tc:SAML:2.0:consent:current-implicit';

    public function create(
        User $user,
        AuthnRequest $authnRequest,
        Provider $identityProvider,
        Provider $serviceProvider,
        Settings $settings
    )
    {
        $serviceProvider = Saml::getInstance()->getProvider()->findByEntityId(
            MessageHelper::getIssuer($authnRequest->getIssuer())
        )->one();

        $response = $this->createGeneral($authnRequest, $identityProvider, $serviceProvider);

        Saml::getInstance()->getResponseAssertion()->create(
            $user,
            $authnRequest,
            $response,
            $identityProvider,
            $serviceProvider,
            $settings
        );


        $response->setSignatureKey(
            $identityProvider->keychainPrivateXmlSecurityKey()
        );

        return $response;
    }

    /**
     * @param AuthnRequest $authnRequest
     * @param Provider $identityProvider
     * @return ResponseMessage
     * @throws \Exception
     */
    protected function createGeneral(AuthnRequest $authnRequest, Provider $identityProvider, Provider $serviceProvider)
    {

        $acsService = $serviceProvider->firstSpAcsService(Constants::BINDING_HTTP_POST) ?? $serviceProvider->firstSpAcsService();
        $response = new ResponseMessage();
        $response->setIssuer(
            $identityProvider->entityId
        );

        $response->setId($requestId = MessageHelper::generateId());
        $response->setDestination(
            $authnRequest->getAssertionConsumerServiceURL() ?? $acsService->getLocation()
        );
        $response->setConsent(static::CONSENT_IMPLICIT);
        $response->setInResponseTo(
            $authnRequest->getId()
        );
        $response->setStatus(
            [
                'Code' => Constants::STATUS_SUCCESS,
            ]
        );
        $response->setIssueInstant(
            (new \DateTime())->getTimestamp()
        );
        $response->setRelayState(
            $authnRequest->getRelayState()
        );

        return $response;
    }


    /**
     * @throws \flipbox\saml\core\exceptions\InvalidMetadata
     */
    public function createAndSendFromSession()
    {
        if (! $authnRequest = Saml::getInstance()->getSession()->getAuthnRequest()) {
            return;
        }

        if (! $user = \Craft::$app->getUser()->getIdentity()) {
            return;
        }

        // load our container
        Saml::getInstance()->loadSaml2Container();

        /** @var ProviderRecord $serviceProvider */
        $serviceProvider = Saml::getInstance()->getProvider()->findByEntityId(
            MessageHelper::getIssuer($authnRequest->getIssuer())
        )->one();

        $identityProvider = Saml::getInstance()->getProvider()->findOwn();

        $response = $this->create(
            $user,
            $authnRequest,
            $identityProvider,
            $serviceProvider,
            Saml::getInstance()->getSettings()
        );

        Factory::send($response, $serviceProvider);
    }
}

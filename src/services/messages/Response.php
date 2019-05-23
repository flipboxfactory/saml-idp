<?php

namespace flipbox\saml\idp\services\messages;

use craft\base\Component;
use craft\elements\User;
use flipbox\saml\core\exceptions\AccessDenied;
use flipbox\saml\core\helpers\MessageHelper;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\core\services\bindings\Factory;
use flipbox\saml\idp\models\Settings;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\records\ProviderRecord as Provider;
use flipbox\saml\idp\Saml;
use SAML2\AuthnRequest as SamlAuthnRequest;
use SAML2\Constants;
use SAML2\Response as ResponseMessage;
use yii\base\Event;

class Response extends Component
{

    const CONSENT_IMPLICIT = Constants::CONSENT_IMPLICIT;
    const EVENT_AFTER_MESSAGE_CREATED = 'eventAfterMessageCreated';

    /**
     * @param User $user
     * @param SamlAuthnRequest $authnRequest
     * @param Provider $identityProvider
     * @param Provider $serviceProvider
     * @param Settings $settings
     * @return ResponseMessage
     * @throws \Exception
     */
    public function create(
        User $user,
        SamlAuthnRequest $authnRequest,
        Provider $identityProvider,
        Provider $serviceProvider,
        Settings $settings
    ) {
        // Check Conditional login on the user
        if (! $this->isAllowed($user, $serviceProvider)) {
            throw new AccessDenied(
                sprintf(
                    'Entity (%s) Access denied for user %s',
                    $serviceProvider->getEntityId(),
                    $user->username
                )
            );
        }


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


        /**
         * Kick off event here so people can manipulate this object if needed
         */
        $event = new Event();
        $event->data = $response;
        $this->trigger(static::EVENT_AFTER_MESSAGE_CREATED, $event);

        return $response;
    }

    /**
     * @param SamlAuthnRequest $authnRequest
     * @param Provider $identityProvider
     * @return ResponseMessage
     * @throws \Exception
     */
    protected function createGeneral(
        SamlAuthnRequest $authnRequest,
        Provider $identityProvider,
        Provider $serviceProvider
    ) {

        $acsService = $serviceProvider->firstSpAcsService(
            Constants::BINDING_HTTP_POST
        ) ?? $serviceProvider->firstSpAcsService();
        $response = new ResponseMessage();
        $response->setIssuer(
            $identityProvider->getEntityId()
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

        // Clear the session
        Saml::getInstance()->getSession()->remove();

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

    /**
     * Utils
     */

    /**
     * @param User $user
     * @param AbstractProvider $serviceProvider
     * @return bool
     */
    protected function isAllowed(User $user, AbstractProvider $serviceProvider): bool
    {
        $options = $serviceProvider->getGroupOptions();
        if ($options->shouldDenyNoGroupAssigned($user)) {
            return false;
        }

        foreach ($user->getGroups() as $group) {
            if (! $options->shouldAllow($group->id)) {
                return false;
            }
        }
        return true;
    }
}

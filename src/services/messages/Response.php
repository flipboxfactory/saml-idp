<?php

namespace flipbox\saml\idp\services\messages;

use craft\base\Component;
use craft\elements\User;
use flipbox\saml\core\exceptions\AccessDenied;
use flipbox\saml\core\helpers\MessageHelper;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\core\services\bindings\Factory;
use flipbox\saml\idp\events\ResponseEvent;
use flipbox\saml\idp\models\Settings;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\records\ProviderRecord as Provider;
use flipbox\saml\idp\Saml;
use SAML2\AuthnRequest as SamlAuthnRequest;
use SAML2\Constants;
use SAML2\Response as ResponseMessage;
use SAML2\XML\saml\Issuer;
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
        Provider $identityProvider,
        Provider $serviceProvider,
        Settings $settings,
        SamlAuthnRequest $authnRequest = null
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


        $response = $this->createGeneral($identityProvider, $serviceProvider, $authnRequest);

        Saml::getInstance()->getResponseAssertion()->create(
            $user,
            $response,
            $identityProvider,
            $serviceProvider,
            $settings,
            $authnRequest
        );


        $response->setSignatureKey(
            $identityProvider->keychainPrivateXmlSecurityKey()
        );

        $response->setCertificates(
            [
                $identityProvider->keychain->getDecryptedCertificate(),
            ]
        );


        /**
         * Kick off event here so people can manipulate this object if needed
         */
        $event = new ResponseEvent();
        $event->response = $response;
        $event->user = $user;
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
        Provider $identityProvider,
        Provider $serviceProvider,
        \SAML2\AuthnRequest $authnRequest = null
    ) {

        $acsService = $serviceProvider->firstSpAcsService(
            Constants::BINDING_HTTP_POST
        ) ?? $serviceProvider->firstSpAcsService();
        $response = new ResponseMessage();
        $issuer = new Issuer();
        $issuer->setFormat(Constants::NAMEID_ENTITY);
        $issuer->setValue($identityProvider->getEntityId());
        $response->setIssuer(
            $issuer
        );

        $response->setId($requestId = MessageHelper::generateId());
        $response->setDestination(
            $authnRequest ? $authnRequest->getAssertionConsumerServiceURL() : $acsService->getLocation()
        );
        $response->setConsent(static::CONSENT_IMPLICIT);
        $response->setStatus(
            [
                'Code' => Constants::STATUS_SUCCESS,
                'Message' => Constants::STATUS_SUCCESS,
            ]
        );
        $response->setIssueInstant(
            (new \DateTime())->getTimestamp()
        );

        return $response;
    }


    /**
     * Utils
     */

    /**
     * @param ResponseMessage $response
     * @param SamlAuthnRequest $authnRequest
     */
    public function finalizeWithAuthnRequest(ResponseMessage $response, SamlAuthnRequest $authnRequest)
    {
        $response->setInResponseTo(
            $authnRequest->getId()
        );
        $response->setRelayState(
            $authnRequest->getRelayState()
        );
    }

    /**
     * @param User $user
     * @param AbstractProvider $serviceProvider
     * @return bool
     */
    protected function isAllowed(User $user, AbstractProvider $serviceProvider): bool
    {
        $options = $serviceProvider->getGroupOptions();
        if ($options->shouldAllowAny()) {
            return true;
        }

        if ($options->shouldAllowNoGroupAssigned($user)) {
            return true;
        }

        foreach ($user->getGroups() as $group) {
            if ($options->shouldAllow($group->id)) {
                return true;
            }
        }
        return false;
    }
}

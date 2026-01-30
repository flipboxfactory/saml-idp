<?php

/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/26/18
 * Time: 3:00 PM
 */

namespace flipbox\saml\idp\services;

use SAML2\Message;
use craft\helpers\Session as SessionHelper;
use SAML2\AuthnRequest;

class Session extends \flipbox\saml\core\services\Session
{
    public const AUTHNREQUEST_KEY = 'authnrequest.message';
    public const RELAY_STATE_KEY = 'relaystate';

    /**
     * @param AuthnRequest $message
     * @return $this
     */
    public function setAuthnRequest(AuthnRequest $message): Session
    {
        $msgStr = $message->toUnsignedXML();
        SessionHelper::set(
            static::AUTHNREQUEST_KEY,
            $msgStr->ownerDocument->saveXML($msgStr),
        );
        return $this;
    }

    /**
     * @return AuthnRequest|null
     */
    public function getAuthnRequest(): ?Message
    {
        $xmlString = SessionHelper::get(
            static::AUTHNREQUEST_KEY,
        );

        if (!$xmlString) {
            return null;
        }

        // Parse the XML string back into a DOMDocument
        $document = new \DOMDocument();
        $document->loadXML($xmlString);

        // Convert back to AuthnRequest object
        return AuthnRequest::fromXML($document->documentElement);
    }

    /**
     * @param string $relayState
     * @return $this
     */
    public function setRelayState(string $relayState): Session
    {
        SessionHelper::set(
            static::RELAY_STATE_KEY,
            $relayState,
        );
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRelayState()
    {
        return SessionHelper::get(
            static::RELAY_STATE_KEY,
        );
    }

    /**
     * @return array
     */
    public function remove()
    {
        return [
            SessionHelper::remove(
                static::AUTHNREQUEST_KEY,
            ),
            SessionHelper::remove(
                static::RELAY_STATE_KEY,
            ),
        ];
    }
}

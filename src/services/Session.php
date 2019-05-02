<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/26/18
 * Time: 3:00 PM
 */

namespace flipbox\saml\idp\services;


use SAML2\AuthnRequest;

class Session extends \flipbox\saml\core\services\Session
{

    const AUTHNREQUEST_KEY = 'authnrequest.message';
    const RELAY_STATE_KEY  = 'relaystate';

    /**
     * @param AuthnRequest $message
     * @return $this
     */
    public function setAuthnRequest(AuthnRequest $message)
    {
        \Craft::$app->getSession()->set(
            static::AUTHNREQUEST_KEY,
            $message
        );
        return $this;
    }

    /**
     * @return AuthnRequest|null
     */
    public function getAuthnRequest()
    {
        return \Craft::$app->getSession()->get(
            static::AUTHNREQUEST_KEY
        );
    }

    /**
     * @param string $relayState
     * @return $this
     */
    public function setRelayState(string $relayState)
    {
        \Craft::$app->getSession()->set(
            static::RELAY_STATE_KEY,
            $relayState
        );
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRelayState()
    {
        return \Craft::$app->getSession()->get(
            static::RELAY_STATE_KEY
        );
    }

    /**
     * @return array
     */
    public function remove()
    {
        return [
            \Craft::$app->getSession()->remove(
                static::AUTHNREQUEST_KEY
            ),
            \Craft::$app->getSession()->remove(
                static::RELAY_STATE_KEY
            ),
        ];
    }

}
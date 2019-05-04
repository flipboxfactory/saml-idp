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
use flipbox\saml\idp\Saml;

/**
 * TODO
 * Class LogoutRequest
 * @package flipbox\saml\idp\services\messages
 */
class LogoutRequest extends Component
{
    public function create(User $user)
    {
        $identity = Saml::getInstance()->getProviderIdentity()->findByUser($user);
        $logout = new LogoutRequestModel();
        $logout->setNameID(
            new NameID(
                $identity->nameId
            )
        );
        $logout->setSessionIndex($identity->sessionId);
    }

    public function createFromSession()
    {
        return $this->create(\Craft::$app->getUser()->getIdentity());
    }
}
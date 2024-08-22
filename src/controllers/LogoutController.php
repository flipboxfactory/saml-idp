<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/10/18
 * Time: 11:52 AM
 */

namespace flipbox\saml\idp\controllers;

use flipbox\saml\core\controllers\messages\AbstractLogoutController;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\idp\traits\SamlPluginEnsured;

/**
 * Class LogoutController
 *
 * @package flipbox\saml\idp\controllers
 */
class LogoutController extends AbstractLogoutController
{
    use SamlPluginEnsured;

    /**
     * @param null $uid
     * @return bool|ProviderInterface
     */
    protected function getRemoteProvider($uid = null)
    {
        $condition = [];
        if ($uid) {
            $condition = [
                'uid' => $uid,
            ];
        }
        return $this->getPlugin()->getProvider()->findBySp($condition)->one();
    }
}

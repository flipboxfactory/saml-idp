<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/9/18
 * Time: 9:11 AM
 */

namespace flipbox\saml\idp;


use Craft;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use craft\web\User;
use flipbox\saml\core\models\SettingsInterface;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\core\services\messages\MetadataServiceInterface;
use flipbox\saml\core\services\ProviderIdentityServiceInterface;
use flipbox\saml\core\services\ProviderServiceInterface;
use flipbox\saml\core\AbstractPlugin;
use flipbox\saml\idp\models\Settings;
use flipbox\saml\idp\services\bindings\HttpPost;
use flipbox\saml\idp\services\Login;
use flipbox\saml\idp\services\messages\AuthnRequest;
use flipbox\saml\idp\services\messages\LogoutRequest;
use flipbox\saml\idp\services\messages\LogoutResponse;
use flipbox\saml\idp\services\messages\Metadata;
use flipbox\saml\idp\services\messages\Response;
use flipbox\saml\idp\services\bindings\HttpRedirect;
use flipbox\saml\idp\services\Provider;
use flipbox\saml\idp\services\ProviderIdentity;
use flipbox\saml\idp\services\Session;
use yii\base\Event;

class Saml extends AbstractPlugin implements SamlPluginInterface
{

    public function init()
    {
        parent::init();

        $this->initComponents();
        $this->initEvents();

        // Switch target to console controllers
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = __NAMESPACE__ . '\cli';
            $this->controllerMap = [
                'metadata' => \flipbox\saml\idp\cli\Metadata::class,
                'keychain' => \flipbox\saml\idp\cli\KeyChain::class,
            ];
        }
    }

    public function initComponents()
    {
        $this->setComponents([
            'authnRequest'     => AuthnRequest::class,
            'httpPost'         => HttpPost::class,
            'httpRedirect'     => HttpRedirect::class,
            'login'            => Login::class,
            'logoutRequest'    => LogoutRequest::class,
            'logoutResponse'   => LogoutResponse::class,
            'provider'         => Provider::class,
            'providerIdentity' => ProviderIdentity::class,
            'metadata'         => Metadata::class,
            'response'         => Response::class,
            'session'          => Session::class,
        ]);
    }

    public function initEvents()
    {
        Event::on(
            User::class,
            User::EVENT_AFTER_LOGIN,
            [
                $this->getResponse(),
                'createAndSendFromSession'
            ]
        );

    }

    /**
     * @return Settings
     */
    public function getSettings(): SettingsInterface
    {
        return parent::getSettings();
    }

    /**
     * @inheritdoc
     */
    public function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Components
     */

    /**
     * @return AuthnRequest
     */
    public function getAuthnRequest()
    {
        return $this->get('authnRequest');
    }

    /**
     * @return HttpPost
     */
    public function getHttpPost()
    {
        return $this->get('httpPost');
    }

    /**
     * @return HttpRedirect
     */
    public function getHttpRedirect()
    {
        return $this->get('httpRedirect');
    }

    /**
     * @return Login
     */
    public function getLogin()
    {
        return $this->get('login');
    }

    /**
     * @return LogoutRequest
     */
    public function getLogoutRequest()
    {
        return $this->get('logoutRequest');
    }

    /**
     * @return LogoutResponse
     */
    public function getLogoutResponse()
    {
        return $this->get('logoutResponse');
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->get('response');
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->get('session');
    }
}

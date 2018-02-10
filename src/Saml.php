<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/9/18
 * Time: 9:11 AM
 */

namespace flipbox\saml\idp;


use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use craft\web\User;
use flipbox\keychain\traits\ModuleTrait as KeyChainModuleTrait;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\core\services\messages\MetadataServiceInterface;
use flipbox\saml\core\services\messages\ProviderServiceInterface;
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

class Saml extends Plugin implements SamlPluginInterface
{
    use KeyChainModuleTrait;

    public function init()
    {
        parent::init();

        $this->initComponents();
        $this->initModules();
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

    public function initModules()
    {
        $this->initKeyChain();
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
            'Response'         => Response::class,
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
                'createAndSend'
            ]
        );

    }

    /**
     * @return Settings
     */
    public function getSettings(): Model
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
     * @returns Provider
     */
    public function getProvider(): ProviderServiceInterface
    {
        return $this->get('provider');
    }

    /**
     * @returns ProviderIdentity
     */
    public function getProviderIdentity()
    {
        return $this->get('providerIdentity');
    }

    /**
     * @return Metadata
     */
    public function getMetadata(): MetadataServiceInterface
    {
        return $this->get('metadata');
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

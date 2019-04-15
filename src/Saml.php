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
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use craft\web\UrlManager;
use craft\web\User;
use flipbox\saml\core\models\SettingsInterface;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\core\services\messages\MetadataServiceInterface;
use flipbox\saml\core\services\ProviderIdentityServiceInterface;
use flipbox\saml\core\services\ProviderServiceInterface;
use flipbox\saml\core\AbstractPlugin;
use flipbox\saml\idp\fields\ExternalIdentity;
use flipbox\saml\idp\models\Settings;
use flipbox\saml\idp\records\ProviderIdentityRecord;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\services\bindings\Factory;
use flipbox\saml\idp\services\bindings\HttpPost;
use flipbox\saml\idp\services\Login;
use flipbox\saml\idp\services\messages\AuthnRequest;
use flipbox\saml\idp\services\messages\LogoutRequest;
use flipbox\saml\idp\services\messages\LogoutResponse;
use flipbox\saml\idp\services\messages\Metadata;
use flipbox\saml\idp\services\messages\Response;
use flipbox\saml\idp\services\bindings\HttpRedirect;
use flipbox\saml\idp\services\messages\ResponseAssertion;
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
        }
    }

    public function initComponents()
    {
        $this->setComponents([
            'authnRequest' => AuthnRequest::class,
            'httpPost' => HttpPost::class,
            'httpRedirect' => HttpRedirect::class,
            'bindingFactory' => Factory::class,
            'login' => Login::class,
            'logoutRequest' => LogoutRequest::class,
            'logoutResponse' => LogoutResponse::class,
            'provider' => Provider::class,
            'providerIdentity' => ProviderIdentity::class,
            'metadata' => Metadata::class,
            'response' => Response::class,
            'responseAssertion' => ResponseAssertion::class,
            'session' => Session::class,
        ]);
    }

    /**
     * Init events
     */
    public function initEvents()
    {
        /**
         * After login
         */
        Event::on(
            User::class,
            User::EVENT_AFTER_LOGIN,
            [
                $this->getResponse(),
                'createAndSendFromSession',
            ]
        );

        /**
         * CP routes
         */
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            [self::class, 'onRegisterCpUrlRules']
        );

        /**
         * Clean Frontend Endpoints
         */
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            [static::class, 'onRegisterSiteUrlRules']
        );

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = ExternalIdentity::class;
            }
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
     * @return ResponseAssertion
     */
    public function getResponseAssertion()
    {
        return $this->get('responseAssertion');
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->get('session');
    }

    /**
     * Util Methods
     */

    public function getMyType()
    {
        return static::IDP;
    }

    /**
     * @return string
     */
    public function getProviderRecordClass()
    {
        return ProviderRecord::class;
    }

    /**
     * @return string
     */
    public function getProviderIdentityRecordClass()
    {
        return ProviderIdentityRecord::class;
    }
}

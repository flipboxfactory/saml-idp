<?php


namespace tests\unit\idp;


use Codeception\Test\Unit;
use flipbox\saml\core\AbstractPlugin;
use flipbox\saml\core\containers\Saml2Container;
use flipbox\saml\core\models\SettingsInterface;
use flipbox\saml\core\services\Metadata;
use flipbox\saml\core\services\Session;
use flipbox\saml\idp\models\Settings;
use flipbox\saml\idp\records\ProviderIdentityRecord;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\Saml;
use flipbox\saml\idp\services\messages\AuthnRequest;
use flipbox\saml\core\services\messages\LogoutRequest;
use flipbox\saml\core\services\messages\LogoutResponse;
use flipbox\saml\idp\services\messages\ResponseAssertion;
use flipbox\saml\idp\services\Provider;
use flipbox\saml\idp\services\ProviderIdentity;
use flipbox\saml\idp\traits\SamlPluginEnsured;
use SAML2\Utils;

class PluginTest extends Unit
{
    /**
     * @var Saml
     */
    private $module;

    const PLUGIN_HANDLE = 'saml-idp';

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->module = new Saml(self::PLUGIN_HANDLE);
    }

    public function testPluginInstance()
    {
        $this->assertInstanceOf(
            Saml::class,
            $this->module
        );

        $this->assertInstanceOf(
            AbstractPlugin::class,
            $this->module
        );

    }

    public function testCraftPluginMethods()
    {
        $this->assertEquals(
            self::PLUGIN_HANDLE,
            $this->module->getHandle()
        );

        $this->assertEquals(
            self::PLUGIN_HANDLE,
            $this->module->getUniqueId()
        );

        $this->assertInstanceOf(
            SettingsInterface::class,
            $this->module->createSettingsModel()
        );

    }

    public function testPluginType()
    {
        $this->assertEquals(SettingsInterface::IDP, $this->module->getMyType());
        $this->assertEquals(SettingsInterface::SP, $this->module->getRemoteType());
    }

    public function testPluginComponents()
    {
        $this->assertInstanceOf(AuthnRequest::class, $this->module->getAuthnRequest());
        $this->assertInstanceOf(LogoutRequest::class, $this->module->getLogoutRequest());
        $this->assertInstanceOf(LogoutResponse::class, $this->module->getLogoutResponse());
        $this->assertInstanceOf(ResponseAssertion::class, $this->module->getResponseAssertion());
        $this->assertInstanceOf(Provider::class, $this->module->getProvider());
        $this->assertInstanceOf(ProviderIdentity::class, $this->module->getProviderIdentity());
        $this->assertInstanceOf(Metadata::class, $this->module->getMetadata());
        $this->assertInstanceOf(Session::class, $this->module->getSession());
        $this->assertInstanceOf(Settings::class, $this->module->getSettings());
    }

    public function testEnsurePlugin()
    {
        $mock = $this->getMockForTrait(SamlPluginEnsured::class);
        $samlPlugin = $mock->getPlugin();
        $this->assertEquals($this->module, $samlPlugin);

        $mock->loadContainer();

        $this->assertInstanceOf(
            Saml2Container::class,
            Utils::getContainer()
        );
    }

    public function testSaml2Container()
    {
        $container = $this->module->loadSaml2Container();

        $this->assertInstanceOf(
            Saml2Container::class,
            $container
        );

        $this->assertEquals(
            $this->module,
            $container->getPlugin()
        );

        $this->assertIsString(
            $container->generateId()
        );

        $this->assertInstanceOf(
            \Psr\Log\LoggerInterface::class,
            $container->getLogger()
        );

    }

    public function testProviderRecordGetter()
    {
        $providerClass = $this->module->getProviderRecordClass();
        $provider = new $providerClass;
        $this->assertInstanceOf(
            ProviderRecord::class,
            $provider
        );

    }

    public function testProviderIdentityRecordGetter()
    {
        $providerIdClass = $this->module->getProviderIdentityRecordClass();
        $providerId = new $providerIdClass;
        $this->assertInstanceOf(
            ProviderIdentityRecord::class,
            $providerId
        );
    }
}
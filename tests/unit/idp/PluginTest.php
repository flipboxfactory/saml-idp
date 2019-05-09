<?php


namespace tests\unit\idp;


use Codeception\Test\Unit;
use flipbox\saml\core\AbstractPlugin;
use flipbox\saml\core\containers\Saml2Container;
use flipbox\saml\core\services\Metadata;
use flipbox\saml\core\services\Session;
use flipbox\saml\idp\models\Settings;
use flipbox\saml\idp\Saml;
use flipbox\saml\idp\services\messages\AuthnRequest;
use flipbox\saml\core\services\messages\LogoutRequest;
use flipbox\saml\core\services\messages\LogoutResponse;
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

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->module = new Saml('saml-idp');
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

    public function testPluginType()
    {
        $this->assertEquals(Settings::IDP, $this->module->getMyType());
    }

    public function testPluginComponents()
    {
        $this->assertInstanceOf(AuthnRequest::class, $this->module->getAuthnRequest());
        $this->assertInstanceOf(LogoutRequest::class, $this->module->getLogoutRequest());
        $this->assertInstanceOf(LogoutResponse::class, $this->module->getLogoutResponse());
        $this->assertInstanceOf(Provider::class, $this->module->getProvider());
        $this->assertInstanceOf(ProviderIdentity::class, $this->module->getProviderIdentity());
        $this->assertInstanceOf(Metadata::class, $this->module->getMetadata());
        $this->assertInstanceOf(Session::class, $this->module->getSession());
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

        $this->assertInternalType(
            'string',
            $container->generateId()
        );

        $this->assertInstanceOf(
            \Psr\Log\LoggerInterface::class,
            $container->getLogger()
        );

    }
}
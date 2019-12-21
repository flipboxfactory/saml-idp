<?php


namespace tests\unit\idp\services;


use Codeception\Scenario;
use Codeception\Test\Unit;
use flipbox\saml\idp\controllers\LoginController;
use flipbox\saml\idp\Saml;
use flipbox\saml\idp\services\Session;
use Step\Unit\Common\AuthnRequest;
use Step\Unit\Common\Metadata;
use Step\Unit\Common\SamlPlugin;

class SessionTest extends Unit
{
    /**
     * @var Saml
     */
    private $module;

    /**
     * @var Metadata
     */
    private $metadataFactory;
    /**
     * @var AuthnRequest
     */
    private $authnRequestFactory;
    /**
     * @var SamlPlugin
     */
    private $pluginHelper;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->module = new Saml('saml-idp');

        $scenario = new Scenario($this);

        $this->metadataFactory = new Metadata($this->module, $scenario);
        $this->pluginHelper = new SamlPlugin($this->module, $scenario);
        $this->authnRequestFactory = new AuthnRequest(
            $this->module,
            $this->metadataFactory,
            $scenario
        );
    }

    public function testSessionAuthnRequest()
    {
        $authnRequest = $this->authnRequestFactory->createAuthnRequest();

        $this->assertInstanceOf(
            Session::class,
            $this->module->getSession()->setAuthnRequest($authnRequest)
        );

        $authnRequest2 = $this->module->getSession()->getAuthnRequest();
        $this->assertEquals(
            $authnRequest,
            $authnRequest2
        );

        $this->module->getSession()->remove();
        $this->assertNull($this->module->getSession()->getAuthnRequest());
        (new LoginController('1234',$this->module))->actionAfterLogin();
    }

    public function testSessionRelayState()
    {
        $relayState = '/my-area-32434';
        $this->module->getSession()->setRelayState($relayState);
        $this->assertEquals(
            $relayState,
            $this->module->getSession()->getRelayState()
        );
    }

    public function testSessionId()
    {
        $this->assertIsString(
            $this->module->getSession()->getId()
        );

        $requestId = '12434';
        $this->module->getSession()->setRequestId($requestId);
        $this->assertEquals(
            $requestId,
            $this->module->getSession()->getRequestId()
        );
    }

}
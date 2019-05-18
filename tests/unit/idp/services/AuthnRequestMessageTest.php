<?php

namespace tests\unit\idp\services;

use Codeception\Scenario;
use Codeception\Test\Unit;
use flipbox\saml\idp\Saml;
use Step\Unit\Common\AuthnRequest;
use Step\Unit\Common\Metadata;
use Step\Unit\Common\SamlPlugin;

class AuthnRequestMessageTest extends Unit
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

    public function testAuthnRequest()
    {

        $serviceProvider = $this->metadataFactory->createTheirProviderWithSigningKey(
            $this->module
        );

        $authnRequest = $this->authnRequestFactory->createAuthnRequest();

        $this->assertTrue(
            $this->module->getAuthnRequest()->isValid($authnRequest, $serviceProvider)
        );
    }
}
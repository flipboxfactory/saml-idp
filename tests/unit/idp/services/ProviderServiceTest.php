<?php

namespace tests\unit\idp\services;

use Codeception\Test\Unit;
use flipbox\saml\idp\Saml;

class ProviderServiceTest extends Unit
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

    public function testGetProvider()
    {

        $providerIdentity = new
        $provider = $this->module->getProvider()->getProvider();

        $this->assertNull($provider);
    }

}
<?php


namespace tests\unit\idp;


use Codeception\Test\Unit;
use flipbox\saml\idp\Saml;

class SettingsTest extends Unit
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

    public function testSettings()
    {
        $settings = $this->module->getSettings();

        $this->assertStringContainsString(
            'overwrite.dev',
            $settings->setEntityId('https://overwrite.dev/')->getEntityId()
        );

        $this->assertIsString(
            $settings->messageNotBefore
        );

        $this->assertIsString(
            $settings->messageNotOnOrAfter
        );

        $this->assertInstanceOf(
            \DateTime::class,
            new \DateTime($settings->messageNotBefore)
        );

        $this->assertInstanceOf(
            \DateTime::class,
            new \DateTime($settings->messageNotOnOrAfter)
        );
    }
}

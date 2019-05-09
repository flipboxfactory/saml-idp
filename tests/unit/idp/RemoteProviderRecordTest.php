<?php


namespace tests\unit\idp;

use Codeception\Scenario;
use Codeception\Test\Unit;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\Saml;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use Step\Unit\Common\Metadata;
use Step\Unit\Common\SamlPlugin;

class RemoteProviderRecordTest extends Unit
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
    }

    public function testRemoteProvider()
    {
        //Install
        $this->pluginHelper->installIfNeeded();

        // With no Key
        $metadata = $this->metadataFactory->createIdpEntityDescriptor();

        $provider = $this->metadataFactory->createIdpProviderWithEntityDescriptor(
            $this->module,
            $metadata
        );

        $this->assertEquals(
            $metadata->getEntityID(),
            $provider->getEntityId()
        );

        $this->assertEquals(
            ProviderRecord::TYPE_IDP,
            $provider->getType()
        );

        // there's no key on this provider
        $this->assertEquals(
            null,
            $provider->signingXMLSecurityKey()
        );

        // With Encryption Key
        $metadata = $this->metadataFactory->createIdpEntityDescriptorWithKeyDescriptors()   ;

        $provider = $this->metadataFactory->createIdpProviderWithEntityDescriptor(
            $this->module,
            $metadata
        );

        // there's no key on this provider
        $this->assertInstanceOf(
            XMLSecurityKey::class,
            $provider->encryptionKey()
        );
    }
}
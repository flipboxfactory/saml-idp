<?php


namespace tests\unit\idp;

use Codeception\Scenario;
use Codeception\Test\Unit;
use flipbox\saml\core\models\SettingsInterface;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\Saml;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Constants;
use SAML2\XML\md\EntityDescriptor;
use SAML2\XML\md\EndpointType;
use Step\Unit\Common\Metadata;
use Step\Unit\Common\SamlPlugin;

class MyProviderRecordTest extends Unit
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

    public function testProviderType()
    {
        $recordClass = $this->module->getProviderRecordClass();
        $this->assertInstanceOf(
            AbstractProvider::class,
            new $recordClass
        );
    }

    public function testProviderMetadataWithKeyPair()
    {
        $this->pluginHelper->installIfNeeded();

        $this->assertInstanceOf(
            EntityDescriptor::class,
            $metadata = $this->metadataFactory->createMyEntityDescriptorWithKey()
        );
    }

    public function testProviderMetadata()
    {
        $this->pluginHelper->installIfNeeded();

        $this->assertInstanceOf(
            EntityDescriptor::class,
            $metadata = $this->metadataFactory->createMyEntityDescriptor()
        );

        $provider = new ProviderRecord();
        $provider->setMetadataModel($metadata);

        $this->assertInstanceOf(
            EntityDescriptor::class,
            $provider->getMetadataModel()
        );

        $this->assertGreaterThan(
            0,
            count($provider->idpSsoDescriptors())
        );

        foreach ($provider->idpSsoDescriptors() as $descriptor) {
            $this->assertGreaterThan(
                0,
                count(
                    $descriptor->getSingleSignOnService()
                )
            );

            /** @var EndpointType $endpoint */
            $endpoint = $descriptor->getSingleSignOnService()[0];

            $this->assertInstanceOf(
                EndpointType::class,
                $endpoint
            );

            $this->assertEquals(
                Constants::BINDING_HTTP_POST,
                $endpoint->getBinding()
            );
        }

    }

    public function testEntityDescriptorTrait()
    {
//        $this->pluginHelper->installIfNeeded();
        $metadata = $this->metadataFactory->createMyEntityDescriptorWithKey();

        $provider = new ProviderRecord([
            'providerType' => SettingsInterface::IDP,
        ]);

        $provider->setMetadataModel($metadata);

        $this->assertGreaterThan(
            0,
            count($provider->idpSsoDescriptors())
        );

        $this->assertStringContainsString(
            '<?xml',
            $provider->toXmlString()
        );

        // to string
        $this->assertStringContainsString(
            '<?xml',
            (string)$provider
        );

        $endpoint = $provider->firstIdpSsoService(Constants::BINDING_HTTP_POST);

        $this->assertEquals(
            $endpoint->getBinding(),
            Constants::BINDING_HTTP_POST
        );

        $endpoint = $provider->firstIdpSloService(Constants::BINDING_HTTP_POST);

        $this->assertEquals(
            $endpoint->getBinding(),
            Constants::BINDING_HTTP_POST
        );

        $xmlSecurityKey = $provider->signingXMLSecurityKey();

        $this->assertInstanceOf(
            XMLSecurityKey::class,
            $xmlSecurityKey
        );

    }

}
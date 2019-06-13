<?php


namespace tests\unit\idp\services;

use Codeception\Scenario;
use Codeception\Test\Unit;
use craft\elements\User;
use craft\models\UserGroup;
use flipbox\saml\core\exceptions\AccessDenied;
use flipbox\saml\core\models\GroupOptions;
use flipbox\saml\idp\Saml;
use SAML2\Response;
use Step\Unit\Common\AuthnRequest;
use Step\Unit\Common\Metadata;
use Step\Unit\Common\SamlPlugin;

class ResponseMessageTest extends Unit
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

    public function testResponseMessage()
    {
        $user = new User([
            'id' => 1,
            'firstName' => 'Damien',
            'lastName' => 'Smrt',
            'email' => 'damien@flipboxdigital.com',
            'username' => 'damien@flipboxdigital.com',
            //Make sure to have a db dump that works with
            'groups' => [
                new UserGroup([
                    'id' => 1,
                    'name' => 'UG1',
                    'handle' => 'ug1',
                ]),
                new UserGroup([
                    'id' => 2,
                    'name' => 'UG2',
                    'handle' => 'ug2',
                ]),
            ],
        ]);
        $sp = $this->metadataFactory->createTheirProviderWithSigningKey($this->module);
        $sp->setGroupOptions(new GroupOptions([
            'options' => [
                'allow' => [
                    1,
                    2,
                ],
            ],
        ]));
        $response = $this->module->getResponse()->create(
            $user,
            $idp = $this->metadataFactory->createMyProviderWithKey($this->module),
            $sp,
            $settings = $this->module->getSettings(),
            $authnRequest = $this->authnRequestFactory->createAuthnRequest()
        );

        $this->assertInstanceOf(Response::class, $response);

        $sp->setGroupOptions(new GroupOptions([
            'options' => [
                'allow' => [
                    1,
                ],
            ],
        ]));

        $this->module->getResponse()->finalizeWithAuthnRequest(
            $response,
            $authnRequest
        );

        $this->expectException(AccessDenied::class);
        $response = $this->module->getResponse()->create(
            $user,
            $idp,
            $sp,
            $settings
        );

    }
}
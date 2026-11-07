<?php

namespace flipbox\saml\idp\tests\unit\controllers;

use Codeception\Scenario;
use Codeception\Test\Unit;
use craft\elements\User;
use flipbox\keychain\KeyChain;
use flipbox\saml\idp\controllers\cp\view\metadata\DefaultController;
use flipbox\saml\idp\controllers\cp\view\metadata\EditController;
use flipbox\saml\idp\controllers\cp\view\metadata\PreviewController;
use flipbox\saml\idp\controllers\LogoutController;
use flipbox\saml\idp\controllers\MetadataController;
use flipbox\saml\idp\controllers\SettingsController;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\Saml;
use Step\Unit\Common\Metadata;
use Step\Unit\Common\SamlPlugin;
use yii\web\ForbiddenHttpException;

class ProviderControllersTest extends Unit
{
    private Saml $module;

    private Metadata $metadata;

    private array $get;

    private bool $requireUserAgentAndIp;

    protected function _before(): void
    {
        $this->get = $_GET;
        $this->module = new Saml('saml-idp');
        $scenario = new Scenario($this);
        (new SamlPlugin($this->module, $scenario))->installIfNeeded();
        $this->module->loadSaml2Container();
        $this->metadata = new Metadata($this->module, $scenario);

        \Craft::$app->getRequest()->setUrl('/admin/saml-idp');
        \Craft::$app->getRequest()->getHeaders()->set('User-Agent', 'Codeception');
        $general = \Craft::$app->getConfig()->getGeneral();
        $this->requireUserAgentAndIp = $general->requireUserAgentAndIpForSession;
        $general->requireUserAgentAndIpForSession = false;
        \Craft::$app->getUser()->logout(false);
    }

    protected function _after(): void
    {
        $_GET = $this->get;
        \Craft::$app->getRequest()->setQueryParams($this->get);
        \Craft::$app->getUser()->logout(false);
        \Craft::$app->getConfig()->getGeneral()->requireUserAgentAndIpForSession = $this->requireUserAgentAndIp;
    }

    /*
     * Logout: the remote party is always a service provider
     */

    public function testLogoutResolvesServiceProvidersByUid(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $controller = new LogoutController('logout', $this->module);

        $this->assertSame($sp->id, $this->callProtected($controller, 'getRemoteProvider', [$sp->uid])->id);
        $this->assertNull(
            $this->callProtected($controller, 'getRemoteProvider', [$idp->uid]),
            'An identity provider uid must not resolve as a logout party'
        );
        $this->assertSame(
            ProviderRecord::TYPE_SP,
            $this->callProtected($controller, 'getRemoteProvider', [])->providerType
        );
    }

    /*
     * Provider record class hooks used by the shared saml-core controllers
     */

    public function testControllersUseTheIdpProviderRecord(): void
    {
        foreach ([
            new MetadataController('metadata', $this->module),
            new SettingsController('settings', $this->module),
            new EditController('edit', $this->module),
        ] as $controller) {
            $this->assertSame(ProviderRecord::class, $this->callProtected($controller, 'getProviderRecord', []), $controller::class);
        }

        $this->assertSame(ProviderRecord::class, (new DefaultController('default', $this->module))->getProviderRecord());
    }

    /*
     * Attribute mapping preview (CP)
     */

    public function testMappingPreviewShowsTheAssertionAttributesForAUser(): void
    {
        [, $sp] = $this->saveProviders();
        $user = $this->loginUser(admin: true);
        $this->setQuery(['userId' => $user->id, 'providerId' => $sp->id]);

        $response = (new PreviewController('preview', $this->module))->actionMapping();

        $xml = new \DOMDocument();
        $this->assertTrue($xml->loadXML($response->data['xml']));
        $values = $this->attributeValues($xml);
        // Mapping on the SP fixture: att1 => email, att2 => firstName, att3 => "{lastName}" template
        $this->assertSame($user->email, $values['att1']);
        $this->assertSame('Preview', $values['att2']);
        $this->assertSame('Person', $values['att3']);
    }

    public function testMappingPreviewRejectsUnknownUserAndProvider(): void
    {
        $this->loginUser(admin: true);
        $this->setQuery(['userId' => 999999, 'providerId' => 999999]);

        $response = (new PreviewController('preview', $this->module))->actionMapping();

        $this->assertSame('Provider or user is invalid', $response->data['error'] ?? $response->data['message'] ?? null);
    }

    public function testMappingPreviewRequiresAnAdmin(): void
    {
        [, $sp] = $this->saveProviders();
        $user = $this->loginUser(admin: false);
        $this->setQuery(['userId' => $user->id, 'providerId' => $sp->id]);

        $this->expectException(ForbiddenHttpException::class);
        (new PreviewController('preview', $this->module))->actionMapping();
    }

    /**
     * @return ProviderRecord[] [this IdP, the SP]
     */
    private function saveProviders(): array
    {
        $idp = $this->metadata->createMyProviderWithKey($this->module);
        $idp->keychain->pluginHandle = $this->module->getHandle();
        KeyChain::getInstance()->getService()->save($idp->keychain);
        $this->module->getProvider()->save($idp);

        $sp = $this->metadata->createTheirProviderWithSigningKey($this->module);
        $this->module->getProvider()->save($sp);

        return [$idp, $sp];
    }

    private function loginUser(bool $admin): User
    {
        $user = new User([
            'username' => uniqid('preview-'),
            'email' => uniqid('preview-') . '@example.com',
            'firstName' => 'Preview',
            'lastName' => 'Person',
            'admin' => $admin,
        ]);
        $this->assertTrue(\Craft::$app->getElements()->saveElement($user, false));
        $this->assertTrue(\Craft::$app->getUser()->login($user));

        return $user;
    }

    private function setQuery(array $params): void
    {
        $_GET = $params;
        \Craft::$app->getRequest()->setQueryParams($params);
    }

    private function attributeValues(\DOMDocument $xml): array
    {
        $xpath = new \DOMXPath($xml);
        $xpath->registerNamespace('saml', 'urn:oasis:names:tc:SAML:2.0:assertion');
        $values = [];
        foreach ($xpath->query('//saml:Attribute') as $attribute) {
            $values[$attribute->getAttribute('Name')] = trim($xpath->query('saml:AttributeValue', $attribute)->item(0)?->textContent ?? '');
        }

        return $values;
    }

    private function callProtected(object $object, string $method, array $args): mixed
    {
        $reflection = new \ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $args);
    }
}

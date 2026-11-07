<?php

namespace flipbox\saml\idp\tests\unit\coverage;

use Codeception\Test\Unit;
use craft\web\Request;
use flipbox\keychain\KeyChain;
use flipbox\saml\core\models\SettingsInterface;
use flipbox\saml\idp\controllers\cp\view\keychain\EditController as KeychainEditController;
use flipbox\saml\idp\controllers\cp\view\keychain\IndexController as KeychainIndexController;
use flipbox\saml\idp\fields\ExternalIdentity;
use flipbox\saml\idp\records\ProviderIdentityRecord;
use flipbox\saml\idp\records\ProviderRecord;

class ProvidersAndPluginTest extends Unit
{
    use IdpFixtures;

    protected function _before(): void
    {
        $this->setUpIdp();
    }

    protected function _after(): void
    {
        $this->setSegments(null);
    }

    public function testServiceProviderRecordsExposeLoginAndLogoutPaths(): void
    {
        $sp = new ProviderRecord(['providerType' => SettingsInterface::SP]);
        $idp = new ProviderRecord(['providerType' => SettingsInterface::IDP]);

        $this->assertSame('sso/login', $sp->getLoginPath());
        $this->assertSame('sso/logout', $sp->getLogoutPath());
        $this->assertNull($idp->getLoginPath());
        $this->assertNull($idp->getLogoutPath());
        $this->assertSame('saml_idp_providers', ProviderRecord::tableAlias());

        // Full endpoint URLs fall back to this plugin's settings (endpoint prefix) and the provider uid
        $sp->generateUid();
        $this->assertStringEndsWith('/sso/login/' . $sp->uid, $sp->getLoginEndpoint());
    }

    public function testFindOwnReturnsThisIdentityProvider(): void
    {
        $idp = $this->saveIdentityProvider();

        $own = $this->module->getProvider()->findOwn();

        $this->assertInstanceOf(ProviderRecord::class, $own);
        $this->assertSame($idp->id, $own->id);
        $this->assertSame($this->module->getSettings()->getEntityId(), $own->getEntityId());
    }

    public function testProviderIdentityLinksUserToServiceProvider(): void
    {
        $sp = $this->serviceProvider();
        $this->module->getProvider()->save($sp);

        $identity = new ProviderIdentityRecord([
            'providerId' => $sp->id,
            'userId' => \Craft::$app->getUsers()->getUserByUsernameOrEmail('craftcms')?->id ?? 1,
            'nameId' => 'jane.doe',
            'enabled' => true,
            'lastLoginDate' => \craft\helpers\Db::prepareDateForDb(new \DateTime()),
        ]);
        $this->assertTrue($identity->save(), json_encode($identity->getErrors()));

        $reloaded = ProviderIdentityRecord::findOne($identity->id);
        $this->assertSame($sp->id, (int)$reloaded->provider->id);
        $this->assertSame(self::SP_ENTITY_ID, $reloaded->provider->getEntityId());
        $this->assertSame(ProviderRecord::class, $this->module->getProviderIdentity()->getRecordClass());
    }

    public function testExternalIdentityFieldTypeIsRegistered(): void
    {
        $this->assertContains(ExternalIdentity::class, \Craft::$app->getFields()->getAllFieldTypes());
    }

    /**
     * @dataProvider keychainNavProvider
     */
    public function testKeychainViewsHighlightTheKeychainSubnav(string $controllerClass, array $segments, ?string $expected): void
    {
        $this->setSegments($segments);
        $controller = new $controllerClass('keychain', $this->module);

        $variables = $this->call($controller, 'beforeRender', ['title' => 'Keys']);

        $this->assertSame($expected, $variables['selectedSubnavItem']);
        $this->assertSame('Keys', $variables['title']);
        $this->assertSame($this->module, $this->call($controller, 'getPlugin'));
    }

    public function keychainNavProvider(): array
    {
        return [
            'index' => [KeychainIndexController::class, ['saml-idp', 'keychain'], 'saml.keychain'],
            'index elsewhere' => [KeychainIndexController::class, ['saml-idp', 'settings'], null],
            'edit' => [KeychainEditController::class, ['saml-idp', 'keychain', '12'], 'saml.keychain'],
            'edit new' => [KeychainEditController::class, ['saml-idp', 'keychain', 'new-openssl'], 'saml.keychain'],
            'edit elsewhere' => [KeychainEditController::class, ['saml-idp', 'metadata', '12'], null],
        ];
    }

    private function saveIdentityProvider(): ProviderRecord
    {
        $idp = $this->identityProvider();
        $idp->keychain->pluginHandle = $this->module->getHandle();
        KeyChain::getInstance()->getService()->save($idp->keychain);
        $this->module->getProvider()->save($idp);

        return $idp;
    }

    private function call(object $object, string $method, mixed ...$args): mixed
    {
        return (new \ReflectionMethod($object, $method))->invoke($object, ...$args);
    }

    /**
     * Craft derives request segments from the URL once; tests set them directly.
     */
    private function setSegments(?array $segments): void
    {
        $request = \Craft::$app->getRequest();
        \Closure::bind(function() use ($segments) {
            if ($segments === null) {
                unset($this->_segments);
            } else {
                $this->_segments = $segments;
            }
        }, $request, Request::class)();
    }
}

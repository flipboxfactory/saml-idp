<?php

namespace flipbox\saml\idp\tests\unit\controllers;

use Codeception\Scenario;
use Codeception\Test\Unit;
use craft\elements\User;
use craft\helpers\UrlHelper;
use flipbox\keychain\KeyChain;
use flipbox\saml\core\exceptions\AccessDenied;
use flipbox\saml\core\exceptions\InvalidMessage;
use flipbox\saml\core\exceptions\InvalidMetadata;
use flipbox\saml\core\models\GroupOptions;
use flipbox\saml\idp\controllers\LoginController;
use flipbox\saml\idp\records\ProviderIdentityRecord;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\Saml;
use SAML2\AuthnRequest as SamlAuthnRequest;
use SAML2\XML\saml\Issuer;
use Step\Unit\Common\Metadata;
use Step\Unit\Common\SamlPlugin;
use yii\base\ExitException;
use yii\base\InlineAction;
use yii\web\ForbiddenHttpException;

class LoginControllerTest extends Unit
{
    private const SP_ENTITY_ID = 'http://sp.localhost:9090/';

    private const SP_ACS_URL = 'http://sp.localhost:9090/sso/login';

    private const IDP_SSO_URL = 'http://www.flipboxlabs.com/idp/sso';

    private const RELAY_STATE = 'http://sp.localhost:9090/after-login';

    private Saml $module;

    private Metadata $metadata;

    private array $server;

    private array $post;

    private array $get;

    private bool $requireUserAgentAndIp;

    protected function _before(): void
    {
        $this->server = $_SERVER;
        $this->post = $_POST;
        $this->get = $_GET;

        $this->module = new Saml('saml-idp');
        $scenario = new Scenario($this);
        (new SamlPlugin($this->module, $scenario))->installIfNeeded();
        $this->module->loadSaml2Container();
        $this->metadata = new Metadata($this->module, $scenario);

        // CLI test requests have no URI; the bindings and login redirects need one
        \Craft::$app->getRequest()->setUrl('/sso/login');

        // CLI test requests have no user agent or IP, which Craft needs for a session
        \Craft::$app->getRequest()->getHeaders()->set('User-Agent', 'Codeception');
        $general = \Craft::$app->getConfig()->getGeneral();
        $this->requireUserAgentAndIp = $general->requireUserAgentAndIpForSession;
        $general->requireUserAgentAndIpForSession = false;

        \Craft::$app->getUser()->logout(false);
        $this->module->getSession()->remove();
        \Craft::$app->getResponse()->data = null;
    }

    protected function _after(): void
    {
        $_SERVER = $this->server;
        $_POST = $this->post;
        $_GET = $this->get;
        \Craft::$app->getRequest()->setBodyParams(null);
        \Craft::$app->getRequest()->setQueryParams($this->get);
        \Craft::$app->getUser()->logout(false);
        \Craft::$app->getConfig()->getGeneral()->requireUserAgentAndIpForSession = $this->requireUserAgentAndIp;
        \Craft::$app->getResponse()->data = null;
    }

    /*
     * actionIndex: SP-initiated SSO endpoint
     */

    public function testLoggedInUserGetsSignedResponseForTheRegisteredServiceProvider(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $user = $this->loginNewUser();
        $request = $this->authnRequest();
        $this->postAuthnRequest($request, self::RELAY_STATE);

        $html = $this->send(fn() => $this->controller()->actionIndex());

        $this->assertPostsTo(self::SP_ACS_URL, $html);
        $response = $this->postedMessage($html, 'SAMLResponse');
        $root = $response->documentElement;
        $this->assertSame('Response', $root->localName);
        $this->assertSame($request->getId(), $root->getAttribute('InResponseTo'));
        $this->assertSame($idp->getEntityId(), $this->xpath($response, '/samlp:Response/saml:Issuer'));
        $this->assertSame('urn:oasis:names:tc:SAML:2.0:status:Success', $this->xpath($response, '/samlp:Response/samlp:Status/samlp:StatusCode/@Value'));
        $this->assertNotSame('', $this->xpath($response, '/samlp:Response/ds:Signature/ds:SignatureValue'), 'The Response must be signed');
        $this->assertSame(self::RELAY_STATE, $this->postedValue($html, 'RelayState'));

        $identity = ProviderIdentityRecord::find()->where(['userId' => $user->id, 'providerId' => $sp->id])->one();
        $this->assertNotNull($identity, 'A provider identity links the user to the SP');
    }

    public function testValidlySignedAuthnRequestIsAccepted(): void
    {
        $this->saveProviders();
        $this->loginNewUser();
        $request = $this->authnRequest();
        $request->setSignatureKey($this->metadata->spPrivateKey());
        $this->postAuthnRequest($request);

        $html = $this->send(fn() => $this->controller()->actionIndex());

        $this->assertSame($request->getId(), $this->postedMessage($html, 'SAMLResponse')->documentElement->getAttribute('InResponseTo'));
    }

    public function testAuthnRequestFromUnknownIssuerIsRejected(): void
    {
        $this->saveProviders();
        $this->loginNewUser();
        $request = $this->authnRequest();
        $request->getIssuer()->setValue('https://unknown-sp.example.com');
        $this->postAuthnRequest($request);

        $this->expectException(InvalidMessage::class);
        $this->expectExceptionMessage('Invalid Issuer.');
        $this->controller()->actionIndex();
    }

    public function testMalformedAuthnRequestIsRejected(): void
    {
        $this->saveProviders();
        $this->loginNewUser();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['SAMLRequest' => base64_encode('<not-saml/>')];
        \Craft::$app->getRequest()->setBodyParams($_POST);

        $this->assertNoResponseIssued(fn() => $this->controller()->actionIndex());
    }

    public function testUserOutsideTheAllowedGroupsIsDenied(): void
    {
        [, $sp] = $this->saveProviders();
        $sp->setGroupOptions(new GroupOptions(['options' => ['allow' => [999999]]]));
        $this->module->getProvider()->save($sp);
        $this->loginNewUser();
        $this->postAuthnRequest($this->authnRequest());

        $this->expectException(AccessDenied::class);
        $this->send(fn() => $this->controller()->actionIndex());
    }

    public function testServiceProviderWithoutAccessRulesAllowsNobody(): void
    {
        [, $sp] = $this->saveProviders();
        $sp->setGroupOptions(new GroupOptions());
        $this->module->getProvider()->save($sp);
        $this->loginNewUser();
        $this->postAuthnRequest($this->authnRequest());

        $this->expectException(AccessDenied::class);
        $this->send(fn() => $this->controller()->actionIndex());
    }

    public function testAnonymousUserIsSentToLoginWithTheRequestSaved(): void
    {
        $this->saveProviders();
        $request = $this->authnRequest();
        $this->postAuthnRequest($request, self::RELAY_STATE);

        $this->controller()->actionIndex();

        $this->assertSame(
            UrlHelper::url(\Craft::$app->getConfig()->getGeneral()->getLoginPath()),
            \Craft::$app->getResponse()->getHeaders()->get('Location')
        );
        $saved = $this->module->getSession()->getAuthnRequest();
        $this->assertNotNull($saved);
        $this->assertSame($request->getId(), $saved->getId());
        $this->assertSame(self::RELAY_STATE, $this->module->getSession()->getRelayState());
        $this->assertStringContainsString('login/after-login', \Craft::$app->getUser()->getReturnUrl());
        $this->assertSame('', (string)\Craft::$app->getResponse()->data, 'No Response may be issued before login');
    }

    /*
     * actionAfterLogin: completes SP-initiated SSO after the user logs in
     */

    public function testAfterLoginIssuesResponseForTheSavedRequest(): void
    {
        $this->saveProviders();
        $request = $this->authnRequest();
        $this->postAuthnRequest($request, self::RELAY_STATE);
        $this->controller()->actionIndex();
        $this->loginNewUser();

        $html = $this->send(fn() => $this->controller()->actionAfterLogin());

        $this->assertPostsTo(self::SP_ACS_URL, $html);
        $this->assertSame($request->getId(), $this->postedMessage($html, 'SAMLResponse')->documentElement->getAttribute('InResponseTo'));
        $this->assertSame(self::RELAY_STATE, $this->postedValue($html, 'RelayState'));
        $this->assertNull($this->module->getSession()->getAuthnRequest(), 'The saved request is single-use');
    }

    public function testAfterLoginFallsBackToTheSavedRelayState(): void
    {
        $this->saveProviders();
        $this->module->getSession()->setAuthnRequest($this->authnRequest());
        $this->module->getSession()->setRelayState(self::RELAY_STATE);
        $this->loginNewUser();

        $html = $this->send(fn() => $this->controller()->actionAfterLogin());

        $this->assertSame(self::RELAY_STATE, $this->postedValue($html, 'RelayState'));
    }

    public function testAfterLoginWithoutSavedRequestIssuesNoResponse(): void
    {
        $this->saveProviders();
        $this->loginNewUser();

        $this->controller()->actionAfterLogin();

        $this->assertSame('', (string)\Craft::$app->getResponse()->data);
    }

    public function testAfterLoginWithoutUserIsRefused(): void
    {
        $this->saveProviders();
        $this->module->getSession()->setAuthnRequest($this->authnRequest());

        try {
            $this->controller()->actionAfterLogin();
            $this->fail('A Response was issued without a logged-in user');
        } catch (ForbiddenHttpException $e) {
            $this->assertSame(403, $e->statusCode);
            $this->assertSame('Unknown Identity.', $e->getMessage());
        }
    }

    /*
     * actionRequest: IdP-initiated SSO
     */

    public function testIdpInitiatedLoginSendsUnsolicitedResponseToTheServiceProvidersAcs(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $user = $this->loginNewUser();
        $this->setQuery(['RelayState' => self::RELAY_STATE]);

        $html = $this->send(fn() => $this->controller()->actionRequest($sp->uid, $idp->uid));

        $this->assertPostsTo(self::SP_ACS_URL, $html);
        $response = $this->postedMessage($html, 'SAMLResponse');
        $this->assertFalse($response->documentElement->hasAttribute('InResponseTo'), 'IdP-initiated responses are unsolicited');
        $this->assertSame($idp->getEntityId(), $this->xpath($response, '/samlp:Response/saml:Issuer'));
        $this->assertSame(self::RELAY_STATE, $this->postedValue($html, 'RelayState'));
        $this->assertNotNull(ProviderIdentityRecord::find()->where(['userId' => $user->id, 'providerId' => $sp->id])->one());
    }

    public function testIdpInitiatedLoginFallsBackToThisIdentityProvider(): void
    {
        [$idp, $sp] = $this->saveProviders();
        $this->loginNewUser();

        $html = $this->send(fn() => $this->controller()->actionRequest($sp->uid, 'not-a-real-uid'));

        $this->assertSame($idp->getEntityId(), $this->xpath($this->postedMessage($html, 'SAMLResponse'), '/samlp:Response/saml:Issuer'));
    }

    public function testIdpInitiatedLoginForUnknownServiceProviderIsRejected(): void
    {
        $this->saveProviders();
        $this->loginNewUser();

        $this->expectException(InvalidMetadata::class);
        $this->expectExceptionMessage('SP Metadata Not found!');
        $this->controller()->actionRequest('not-a-real-uid');
    }

    public function testIdpInitiatedLoginWithoutAnIdentityProviderIsRejected(): void
    {
        [, $sp] = $this->saveProviders();
        $this->module->getSettings()->setEntityId('https://not-this-idp.example.com');
        $this->loginNewUser();

        $this->expectException(InvalidMetadata::class);
        $this->expectExceptionMessage('IdP Metadata Not found!');
        $this->controller()->actionRequest($sp->uid);
    }

    public function testIdpInitiatedLoginSendsAnonymousUserToLogin(): void
    {
        [, $sp] = $this->saveProviders();

        $this->controller()->actionRequest($sp->uid);

        $this->assertSame(
            UrlHelper::url(\Craft::$app->getConfig()->getGeneral()->getLoginPath()),
            \Craft::$app->getResponse()->getHeaders()->get('Location')
        );
        $this->assertSame('', (string)\Craft::$app->getResponse()->data, 'No Response may be issued before login');
    }

    /*
     * Access
     */

    public function testSsoEndpointsAllowAnonymousAccess(): void
    {
        $controller = $this->controller();

        foreach (['index', 'request', 'after-login'] as $action) {
            $this->assertTrue($controller->beforeAction(new InlineAction($action, $controller, 'action')), $action);
        }
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
        $sp->setGroupOptions(new GroupOptions(['options' => ['allow' => [GroupOptions::ALLOW_ANY]]]));
        $this->module->getProvider()->save($sp);

        return [$idp, $sp];
    }

    private function loginNewUser(): User
    {
        $user = new User([
            'username' => 'idp-user-' . uniqid(),
            'email' => uniqid('idp-user-') . '@example.com',
            'firstName' => 'Idp',
            'lastName' => 'User',
        ]);
        $this->assertTrue(\Craft::$app->getElements()->saveElement($user, false));
        $this->assertTrue(\Craft::$app->getUser()->login($user));

        return $user;
    }

    private function authnRequest(): SamlAuthnRequest
    {
        $issuer = new Issuer();
        $issuer->setValue(self::SP_ENTITY_ID);
        $request = new SamlAuthnRequest();
        $request->setIssuer($issuer);
        $request->setDestination(self::IDP_SSO_URL);
        $request->setAssertionConsumerServiceURL(self::SP_ACS_URL);

        return $request;
    }

    private function postAuthnRequest(SamlAuthnRequest $request, ?string $relayState = null): void
    {
        $xml = $request->toSignedXML();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['SAMLRequest' => base64_encode($xml->ownerDocument->saveXML($xml))];
        if ($relayState !== null) {
            $_POST['RelayState'] = $relayState;
        }
        \Craft::$app->getRequest()->setBodyParams($_POST);
    }

    private function setQuery(array $params): void
    {
        $_GET = $params;
        \Craft::$app->getRequest()->setQueryParams($params);
    }

    /**
     * Runs an action that sends a message through the HTTP-POST binding, which renders an
     * auto-submit form and ends the request.
     */
    private function send(callable $action): string
    {
        ob_start();
        try {
            $action();
            $this->fail('Expected the request to end after sending the message.');
        } catch (ExitException) {
        } finally {
            $output = ob_get_clean();
        }

        return (string)(\Craft::$app->getResponse()->data ?: $output);
    }

    private function assertNoResponseIssued(callable $action): void
    {
        ob_start();
        try {
            $action();
            $this->fail('The AuthnRequest was accepted.');
        } catch (\PHPUnit\Framework\AssertionFailedError $e) {
            throw $e;
        } catch (ExitException) {
            $this->fail('A Response was sent.');
        } catch (\Throwable) {
            // rejected
        } finally {
            ob_end_clean();
        }

        $this->assertStringNotContainsString('SAMLResponse', (string)\Craft::$app->getResponse()->data);
    }

    private function assertPostsTo(string $url, string $html): void
    {
        $this->assertMatchesRegularExpression('/<form[^>]+action="' . preg_quote($url, '/') . '"/', $html);
    }

    private function postedValue(string $html, string $name): string
    {
        $pattern = '/name="' . $name . '" value="([^"]*)"/';
        $this->assertMatchesRegularExpression($pattern, $html);
        preg_match($pattern, $html, $matches);

        return html_entity_decode($matches[1]);
    }

    private function postedMessage(string $html, string $name): \DOMDocument
    {
        $document = new \DOMDocument();
        $this->assertTrue($document->loadXML(base64_decode($this->postedValue($html, $name), true)));

        return $document;
    }

    private function xpath(\DOMDocument $document, string $query): string
    {
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('samlp', 'urn:oasis:names:tc:SAML:2.0:protocol');
        $xpath->registerNamespace('saml', 'urn:oasis:names:tc:SAML:2.0:assertion');
        $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
        $nodes = $xpath->query($query);

        return $nodes && $nodes->length ? trim($nodes->item(0)->textContent) : '';
    }

    private function controller(): LoginController
    {
        return new LoginController('login', $this->module);
    }
}

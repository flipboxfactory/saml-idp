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
        // Without this the test only passes when an earlier test happened to load the container
        $this->module->loadSaml2Container();

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

        // The session stores the request as XML, so compare identity and content
        // rather than in-memory state (signing key, DOM document)
        $authnRequest2 = $this->module->getSession()->getAuthnRequest();
        $this->assertSame($authnRequest->getId(), $authnRequest2->getId());
        $original = $authnRequest->toUnsignedXML();
        $restored = $authnRequest2->toUnsignedXML();
        $this->assertSame($original->ownerDocument->saveXML($original), $restored->ownerDocument->saveXML($restored));

        $this->module->getSession()->remove();
        $this->assertNull($this->module->getSession()->getAuthnRequest());
        // Without a logged-in user the IdP must refuse to issue a Response
        $this->expectException(\yii\web\ForbiddenHttpException::class);
        (new LoginController('1234', $this->module))->actionAfterLogin();
    }

    public function testSessionRefusesAMessageThatIsNotAnAuthnRequest(): void
    {
        $response = (new \SAML2\Response())->toUnsignedXML();
        \craft\helpers\Session::set(
            Session::AUTHNREQUEST_KEY,
            $response->ownerDocument->saveXML($response),
        );

        $this->assertNull($this->module->getSession()->getAuthnRequest());
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

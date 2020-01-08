<?php


namespace flipbox\saml\idp\controllers\cp\view\metadata;

use flipbox\saml\core\controllers\cp\view\metadata\AbstractPreviewController;
use flipbox\saml\idp\Saml;
use flipbox\saml\idp\traits\SamlPluginEnsured;
use SAML2\Assertion;

class PreviewController extends AbstractPreviewController
{
    use SamlPluginEnsured;

    public function actionMapping()
    {
        $this->requireAdmin(false);

        Saml::getInstance()->loadSaml2Container();
        $settings = Saml::getInstance()->getSettings();

        $userId = \Craft::$app->request->getRequiredParam('userId');
        $providerId = \Craft::$app->request->getRequiredParam('providerId');

        $user = \Craft::$app->users->getUserById($userId);
        $provider = Saml::getInstance()->getProvider()->find([
            'id' => $providerId,
        ])->one();

        if (! $user && ! $provider) {
            return $this->asErrorJson('Provider or user is invalid');
        }

        Saml::getInstance()->getResponseAssertion()->setAssertionAttributes(
            $user,
            $assertion = new Assertion(),
            $provider,
            $settings
        );
        $doc = $assertion->toXML()->ownerDocument;
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        return $this->asJson([
            'xml' => $doc->saveXML(),
        ]);
    }
}

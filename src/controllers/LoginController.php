<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/10/18
 * Time: 11:52 AM
 */

namespace flipbox\saml\idp\controllers;

use Craft;
use flipbox\saml\core\controllers\messages\AbstractController;
use flipbox\saml\core\helpers\MessageHelper;
use flipbox\saml\core\services\bindings\Factory;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\Saml;
use flipbox\saml\idp\traits\SamlPluginEnsured;
use SAML2\AuthnRequest;

class LoginController extends AbstractController
{
    use SamlPluginEnsured;

    protected $allowAnonymous = [
        'actionIndex',
    ];

    public $enableCsrfValidation = false;

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        return true;
    }

    /**
     * @throws \flipbox\saml\core\exceptions\InvalidMessage
     * @throws \flipbox\saml\core\exceptions\InvalidMetadata
     */
    public function actionIndex()
    {

        /** @var AuthnRequest $authnRequest */
        $authnRequest = Factory::receive();

        Saml::getInstance()->getAuthnRequest()->isValid($authnRequest);

        /**
         * Check relay state
         */
        if ($relayState = \Craft::$app->request->getParam('RelayState')) {
            try {
                $relayStateDecoded = base64_decode($relayState);
                $authnRequest->setRelayState($relayState);
                Saml::info('RelayState: ' . $relayStateDecoded);
                Saml::info('RelayState from authnRequest: ' . $authnRequest->getRelayState());
            } catch (\Exception $e) {
                Saml::warning(
                    sprintf(
                        'RelayState must be base64 encoded: %s',
                        is_string($relayState) ? $relayState : ''
                    )
                );
            }
        }

        if ($user = Craft::$app->getUser()->getIdentity()) {

            /** @var ProviderRecord $serviceProvider */
            $serviceProvider = Saml::getInstance()->getProvider()->findByEntityId(
                MessageHelper::getIssuer($authnRequest->getIssuer())
            )->one();

            $identityProvider = Saml::getInstance()->getProvider()->findOwn();

            //create response and send back to the sp
            $response = Saml::getInstance()->getResponse()->create(
                $user,
                $authnRequest,
                $identityProvider,
                $serviceProvider,
                Saml::getInstance()->getSettings()
            );

            Factory::send($response, $serviceProvider);
            return;
        }

        //save to session and redirect to login
        Saml::getInstance()->getSession()->setAuthnRequest($authnRequest);

        $this->redirect(
            Craft::$app->config->general->getLoginPath()
        );
        return;
    }
}

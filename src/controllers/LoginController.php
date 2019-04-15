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
use flipbox\saml\idp\Saml;
use flipbox\saml\idp\traits\SamlPluginEnsured;
use flipbox\saml\sp\records\ProviderRecord;

class LoginController extends AbstractController
{
    use SamlPluginEnsured;

    protected $allowAnonymous = [
        'actionIndex',
        'actionRequest',
    ];

    public $enableCsrfValidation = false;

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        $this->logRequest();
        return true;
    }

    /**
     * @return void|\yii\web\Response
     * @throws \Exception
     * @throws \flipbox\saml\core\exceptions\InvalidMessage
     * @throws \flipbox\saml\core\exceptions\InvalidMetadata
     * @throws \flipbox\saml\core\exceptions\InvalidSignature
     */
    public function actionIndex()
    {

        $authnRequest = Saml::getInstance()->getBindingFactory()->receive(
            Craft::$app->request
        );

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

            //create response and send back to the sp
            $response = Saml::getInstance()->getResponse()->create($authnRequest);

            /** @var ProviderRecord $spProvider */
            $spProvider = Saml::getInstance()->getProvider()->findByEntityId(
                $authnRequest->getIssuer()->getValue()
            )->one();

            Saml::getInstance()->getBindingFactory()->send($response, $spProvider);
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
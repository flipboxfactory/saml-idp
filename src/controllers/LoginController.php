<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/10/18
 * Time: 11:52 AM
 */

namespace flipbox\saml\idp\controllers;


use craft\web\Controller;
use flipbox\saml\idp\Saml;
use Craft;
use flipbox\saml\idp\services\bindings\Factory;
use yii\web\HttpException;

class LoginController extends Controller
{

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
        return true;
    }

    public function actionIndex()
    {

        $response = Saml::getInstance()->getResponse()->parseByRequest(Craft::$app->request);
        if (! Saml::getInstance()->getAuthnRequest()->isResponseValidWithSession($response)) {
            throw new HttpException(400, "Invalid request");
        }

        Saml::getInstance()->getLogin()->login($response);

        //get relay state but don't error!
        $relayState = \Craft::$app->request->getQueryParam('RelayState') ?: \Craft::$app->request->getBodyParam('RelayState');
        try {
            $redirect = base64_decode($relayState);
        } catch (\Exception $e) {
            $redirect = \Craft::$app->getUser()->getReturnUrl();
        }

//        $this->renderTemplate()
        return $this->redirect($redirect);
    }

    public function actionRequest()
    {

        $authnRequest = Factory::receive(Craft::$app->request);

        Saml::getInstance()->getAuthnRequest()->isValid($authnRequest);

        if ($user = Craft::$app->getUser()->getIdentity()) {
            //create response and send back to the sp
            Saml::getInstance()->getResponse()->createAndSend($authnRequest);
            return;
        }

        //save to session and redirect to login
        Saml::getInstance()->getSession()->setAuthnRequest($authnRequest);

        return $this->redirect(
            Craft::$app->config->general->getLoginPath()
        );
    }

}
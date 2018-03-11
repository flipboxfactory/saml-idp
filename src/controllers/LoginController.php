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

    /**
     * @return void|\yii\web\Response
     * @throws \Exception
     * @throws \flipbox\saml\core\exceptions\InvalidMessage
     * @throws \flipbox\saml\core\exceptions\InvalidMetadata
     * @throws \flipbox\saml\core\exceptions\InvalidSignature
     */
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
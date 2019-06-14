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
use flipbox\saml\core\exceptions\InvalidMessage;
use flipbox\saml\core\helpers\MessageHelper;
use flipbox\saml\core\helpers\SerializeHelper;
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
     * @throws \flipbox\saml\core\exceptions\InvalidMessage
     * @throws \flipbox\saml\core\exceptions\InvalidMetadata
     */
    public function actionIndex()
    {

        /** @var AuthnRequest $authnRequest */
        $authnRequest = Factory::receive();

        /** @var ProviderRecord $serviceProvider */
        $serviceProvider = Saml::getInstance()->getProvider()->findByEntityId(
            MessageHelper::getIssuer($authnRequest->getIssuer())
        )->one();

        if (is_null($serviceProvider)) {
            throw new InvalidMessage("Invalid Issuer.");
        }

        Saml::getInstance()->getAuthnRequest()->isValid($authnRequest, $serviceProvider);

        /**
         * Check relay state
         */

        if ($relayState = $this->getRelayState()) {
            $authnRequest->setRelayState($relayState);
        }

        if ($user = Craft::$app->getUser()->getIdentity()) {
            $identityProvider = Saml::getInstance()->getProvider()->findOwn();

            //create response and send back to the sp
            $response = Saml::getInstance()->getResponse()->create(
                $user,
                $identityProvider,
                $serviceProvider,
                Saml::getInstance()->getSettings(),
                $authnRequest
            );

            Saml::getInstance()->getResponse()->finalizeWithAuthnRequest($response, $authnRequest);

            $identity = Saml::getInstance()->getProviderIdentity()->findByUserAndProviderOrCreate(
                $user,
                $serviceProvider
            );

            Saml::getInstance()->getProviderIdentity()->save($identity);

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

    public function actionRequest($uid)
    {
        //build uid condition
        $uidCondition = [
            'uid' => $uid,
        ];

        /**
         * @var ProviderRecord $sp
         */
        if (! $serviceProvider = Saml::getInstance()->getProvider()->findBySp(
            $uidCondition
        )->one()
        ) {
            throw new InvalidMetadata('IDP Metadata Not found!');
        }

        if ($user = Craft::$app->getUser()->getIdentity()) {
            $identityProvider = Saml::getInstance()->getProvider()->findOwn();

            //create response and send back to the sp
            $response = Saml::getInstance()->getResponse()->create(
                $user,
                $identityProvider,
                $serviceProvider,
                Saml::getInstance()->getSettings()
            );

            $response->setRelayState($this->getRelayState());

            $identity = Saml::getInstance()->getProviderIdentity()->findByUserAndProviderOrCreate(
                $user,
                $serviceProvider
            );

            Saml::getInstance()->getProviderIdentity()->save($identity);

            Factory::send($response, $serviceProvider);
            return;
        }

        //save to session and redirect to login
        \Craft::$app->user->setReturnUrl(
            \Craft::$app->request->getAbsoluteUrl()
        );

        $this->redirect(
            Craft::$app->config->general->getLoginPath()
        );
        return;
    }

    /**
     * @return string
     */
    protected function getRelayState(): string
    {
        $relayState = \Craft::$app->request->getParam('RelayState');
        if (is_string($relayState) && ! empty($relayState)) {
            try {
                // if it's not base64'd we need to encode it.
                $relayState = SerializeHelper::isBase64String($relayState) ? $relayState : base64_encode($relayState);
                Saml::info('RelayState: ' . $relayState);
            } catch (\Exception $e) {
                Saml::info(
                    sprintf(
                        'Error with relay state: %s - %s',
                        (is_string($relayState) ? $relayState : ''),
                        $e->getTraceAsString()
                    )
                );
            }
        } else {
            $relayState = '';
        }
        return $relayState;
    }
}

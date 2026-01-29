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
use flipbox\saml\core\exceptions\InvalidMetadata;
use flipbox\saml\core\helpers\MessageHelper;
use flipbox\saml\core\helpers\UrlHelper as SamlUrlHelper;
use craft\helpers\UrlHelper;
use flipbox\saml\core\services\bindings\Factory;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\Saml;
use flipbox\saml\idp\traits\SamlPluginEnsured;
use SAML2\AuthnRequest;
use yii\web\HttpException;

class LoginController extends AbstractController
{
    use SamlPluginEnsured;

    protected array|int|bool $allowAnonymous = [
        'index',
        'request',
        'after-login',
    ];

    public $enableCsrfValidation = false;

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action): bool
    {
        // Always call parent beforeAction - important for framework functionality
        return parent::beforeAction($action);
    }

    /**
     * @throws \flipbox\saml\core\exceptions\InvalidMessage
     * @throws \flipbox\saml\core\exceptions\InvalidMetadata
     */
    public function actionIndex()
    {
        // Load SAML2 container early to ensure proper serialization
        Saml::getInstance()->loadSaml2Container();

        // Debug: Check if logging is working at all
        Saml::info("=== actionIndex START ===");

        /** @var AuthnRequest $authnRequest */
        $authnRequest = Factory::receive();

        /** @var ProviderRecord $serviceProvider */
        $serviceProvider = Saml::getInstance()->getProvider()->findByEntityId(
            MessageHelper::getIssuer($authnRequest->getIssuer()),
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
                $authnRequest,
            );

            Saml::getInstance()->getResponse()->finalizeWithAuthnRequest($response, $authnRequest);

            $identity = Saml::getInstance()->getProviderIdentity()->findByUserAndProviderOrCreate(
                $user,
                $serviceProvider,
            );

            Saml::getInstance()->getProviderIdentity()->save($identity);

            Factory::send($response, $serviceProvider);
            return;
        }

        //save to session and redirect to login
        Saml::info("Saving AuthnRequest to session");
        Saml::getInstance()->getSession()->setAuthnRequest($authnRequest);

        \Craft::$app->user->setReturnUrl(
            UrlHelper::actionUrl(
                Saml::getInstance()->getHandle() . '/login/after-login',
            ),
        );

        Saml::info("Setting return URL and closing session");
        // Close session to ensure data is written before redirect
        \Craft::$app->session->close();

        $this->redirect(
            Craft::$app->config->general->getLoginPath(),
        );
        return;
    }

    public function actionAfterLogin()
    {
        // Load SAML2 container BEFORE accessing session to ensure proper deserialization
        Saml::getInstance()->loadSaml2Container();

        Saml::info("=== actionAfterLogin START ===");

        if (!$authnRequest = Saml::getInstance()->getSession()->getAuthnRequest()) {
            Saml::warning("AuthnRequest not found in session");
            Saml::error("CRITICAL: Session data lost - AuthnRequest is null");
            return;
        }

        Saml::info("AuthnRequest successfully retrieved from session");

        // Clear the session
        /* try { */
        /*     Saml::getInstance()->getSession()->remove(); */
        /* } catch (Exception $e) { */
        /*     Saml::error($e->getMessage()); */
        /* } */

        if (!$user = \Craft::$app->getUser()->getIdentity()) {
            throw new HttpException('Unknown Identity.');
        }

        /** @var ProviderRecord $serviceProvider */
        $serviceProvider = Saml::getInstance()->getProvider()->findByEntityId(
            MessageHelper::getIssuer($authnRequest->getIssuer()),
        )->one();

        $identityProvider = Saml::getInstance()->getProvider()->findOwn();

        $response = Saml::getInstance()->getResponse()->create(
            $user,
            $identityProvider,
            $serviceProvider,
            Saml::getInstance()->getSettings(),
            $authnRequest,
        );

        Saml::getInstance()->getResponse()->finalizeWithAuthnRequest($response, $authnRequest);

        Factory::send($response, $serviceProvider);
    }

    /**
     * @param string $externalUid SP UID
     * @param string|null $internalUid IdP UID
     * @throws InvalidMetadata
     */
    public function actionRequest(string $externalUid, string $internalUid = null)
    {
        //build uid condition
        $uidCondition = [
            'uid' => $externalUid,
        ];

        /**
         * @var ProviderRecord $sp
         */
        if (!$serviceProvider = Saml::getInstance()->getProvider()->findBySp(
            $uidCondition,
        )->one()
        ) {
            throw new InvalidMetadata('SP Metadata Not found!');
        }

        if ($user = Craft::$app->getUser()->getIdentity()) {
            $identityProvider = Saml::getInstance()->getProvider()->findByIdp([
                'uid' => $internalUid,
            ])->one() ?? Saml::getInstance()->getProvider()->findOwn();

            if (!$identityProvider) {
                throw new InvalidMetadata('IdP Metadata Not found!');
            }


            //create response and send back to the sp
            $response = Saml::getInstance()->getResponse()->create(
                $user,
                $identityProvider,
                $serviceProvider,
                Saml::getInstance()->getSettings(),
            );

            $response->setRelayState($this->getRelayState());

            $identity = Saml::getInstance()->getProviderIdentity()->findByUserAndProviderOrCreate(
                $user,
                $serviceProvider,
            );

            Saml::getInstance()->getProviderIdentity()->save($identity);

            Factory::send($response, $serviceProvider);
            return;
        }

        //save to session and redirect to login
        \Craft::$app->user->setReturnUrl(
            \Craft::$app->request->getAbsoluteUrl(),
        );

        $this->redirect(
            Craft::$app->config->general->getLoginPath(),
        );
        return;
    }

    /**
     * @return string
     */
    protected function getRelayState(): string
    {
        return \Craft::$app->request->getParam('RelayState') ?? '';
    }
}

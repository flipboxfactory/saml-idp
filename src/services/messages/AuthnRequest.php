<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/10/18
 * Time: 11:23 AM
 */

namespace flipbox\saml\idp\services\messages;


use craft\base\Component;
use flipbox\saml\core\exceptions\InvalidMessage;
use flipbox\saml\idp\models\Settings;
use flipbox\saml\idp\Saml;
use flipbox\saml\core\services\traits\Security;
use LightSaml\Credential\X509Certificate;
use LightSaml\Helper;
use LightSaml\Model\Assertion\Issuer;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use yii\validators\Validator;
use yii\web\Request;

class AuthnRequest extends Component
{

    use Security;

    /**
     * @return XMLSecurityKey
     */
    public function getKey(): XMLSecurityKey
    {
        return \LightSaml\Credential\KeyHelper::createPrivateKey(
            Saml::getInstance()->getSettings()->keyPath,
            '',
            true
        );
    }

    /**
     * @return X509Certificate
     */
    public function getCertificate(): X509Certificate
    {
        return X509Certificate::fromFile(
            Saml::getInstance()->getSettings()->certPath
        );
    }

    public function isValid(\LightSaml\Model\Protocol\AuthnRequest $authnRequest) {
        if( ! ($provider = Saml::getInstance()->getProvider()->findByIssuer(
            $authnRequest->getIssuer()
        ))) {
            throw new InvalidMessage("Invalid Message.");
        }

        if($authnRequest->getSignature()){
            if($this->validSignature($authnRequest, $provider)) {
                throw new InvalidMessage("Invalid Message.");
            }
        }

        return true;
    }

    /**
     * @param \craft\web\Request $request
     * @return \LightSaml\Model\Protocol\AuthnRequest
     */
    public function parseByRequest(\craft\web\Request $request) : \LightSaml\Model\Protocol\AuthnRequest
    {

        switch ($request->getMethod()) {
            case 'POST':
                $authnRequest = Saml::getInstance()->getHttpPost()->receive($request);
                break;
            case 'GET':
            default:
                $authnRequest = Saml::getInstance()->getHttpRedirect()->receive($request);
                break;
        }

        if (! ($authnRequest instanceof \LightSaml\Model\Protocol\AuthnRequest)) {
            throw new InvalidMessage("Invalid Message.");
        }

        return $authnRequest;
    }

    public function create(string $entityId = null)
    {
        if (! $entityId) {

            /**
             * @var \flipbox\saml\sp\models\Provider $provider
             */
            if (! $provider = Saml::getInstance()->getProvider()->findDefaultProvider()) {
                return null;
            }

        } else {
            $provider = Saml::getInstance()->getProvider()->findByString($entityId);
        }

        $location = $provider->getMetadata()->getFirstIdpSsoDescriptor()->getFirstSingleSignOnService()->getLocation();

        /**
         * @var $samlSettings Settings
         */
        $samlSettings = Saml::getInstance()->getSettings();
        $authnRequest = new \LightSaml\Model\Protocol\AuthnRequest();

        $authnRequest->setAssertionConsumerServiceURL(
            Metadata::getLoginLocation()
        )->setProtocolBinding(
            $provider->getMetadata()->getFirstIdpSsoDescriptor()->getFirstSingleSignOnService()->getBinding()
        )->setID(Helper::generateID())
            ->setIssueInstant(new \DateTime())
            ->setDestination($location)
            ->setIssuer(new Issuer($samlSettings->getEntityId()));

        //set signed assertions
        if ($samlSettings->signAssertions) {
            $this->signMessage($authnRequest);
        }

        return $authnRequest;
    }

}
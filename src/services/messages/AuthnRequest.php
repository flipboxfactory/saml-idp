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
use flipbox\saml\core\helpers\SecurityHelper;
use flipbox\saml\idp\models\Settings;
use flipbox\saml\idp\Saml;
use LightSaml\Helper;
use LightSaml\Model\Assertion\Issuer;
use LightSaml\Model\Metadata\KeyDescriptor;

class AuthnRequest extends Component
{

    /**
     * @param \LightSaml\Model\Protocol\AuthnRequest $authnRequest
     * @return bool
     * @throws InvalidMessage
     */
    public function isValid(\LightSaml\Model\Protocol\AuthnRequest $authnRequest)
    {
        if (! ($provider = Saml::getInstance()->getProvider()->findByEntityId(
            $authnRequest->getIssuer()->getValue()
        ))) {
            throw new InvalidMessage("Invalid Message.");
        }

        if ($authnRequest->getSignature()) {
            if (
                ! SecurityHelper::validSignature(
                    $authnRequest,
                    $provider->getMetadataModel()->getFirstSpSsoDescriptor()->getFirstKeyDescriptor(KeyDescriptor::USE_SIGNING)
                )
            ) {
                throw new InvalidMessage("Invalid Message.");
            }
        }

        return true;
    }

    /**
     * @param \craft\web\Request $request
     * @return \LightSaml\Model\Protocol\AuthnRequest
     */
    public function parseByRequest(\craft\web\Request $request): \LightSaml\Model\Protocol\AuthnRequest
    {



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
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
}
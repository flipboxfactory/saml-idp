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
use flipbox\saml\core\helpers\MessageHelper;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\Saml;
use SAML2\AuthnRequest as SamlAuthnRequest;
use SAML2\Utils;

class AuthnRequest extends Component
{

    public function isValid(SamlAuthnRequest $authnRequest)
    {
        /** @var ProviderRecord $sp */
        if (! ($sp = Saml::getInstance()->getProvider()->findByEntityId(
            MessageHelper::getIssuer($authnRequest->getIssuer())
        )->one())) {
            throw new InvalidMessage("Invalid Message.");
        }

        //TODO validate Destination

        // Validate Signature
        $signingKey = $sp->signingXMLSecurityKey();

        if ($signingKey && ($sig = Utils::validateElement($authnRequest->toSignedXML()))) {
            $authnRequest->addValidator(
                [
                    Utils::class,
                    'validateSignature',
                ],
                $sig
            );

            $authnRequest->validate($signingKey);
        }

        return true;
    }
}

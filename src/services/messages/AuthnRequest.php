<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/10/18
 * Time: 11:23 AM
 */

namespace flipbox\saml\idp\services\messages;

use craft\base\Component;
use flipbox\saml\core\records\AbstractProvider;
use SAML2\AuthnRequest as SamlAuthnRequest;
use SAML2\Utils;

class AuthnRequest extends Component
{
    public function isValid(SamlAuthnRequest $authnRequest, AbstractProvider $serviceProvider)
    {
        //TODO validate Destination

        // Validate Signature
        $signingKey = $serviceProvider->signingXMLSecurityKey();

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

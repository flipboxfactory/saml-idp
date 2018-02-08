<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/11/18
 * Time: 9:44 PM
 */

namespace flipbox\saml\idp\services\bindings;


use craft\web\Request;
use flipbox\saml\core\exceptions\InvalidIssuer;
use flipbox\saml\core\services\bindings\AbstractHttpRedirect;
use flipbox\saml\sp\Saml;
use flipbox\saml\sp\services\traits\Security;
use LightSaml\Error\LightSamlBindingException;
use LightSaml\Model\Assertion\Issuer;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Protocol\SamlMessage;
use LightSaml\Model\XmlDSig\SignatureStringReader;
use LightSaml\SamlConstants;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use LightSaml\Credential\X509Certificate;

class HttpRedirect extends AbstractHttpRedirect
{
    /**
     * @inheritdoc
     */
    public function getProviderByIssuer(Issuer $issuer): ProviderInterface
    {
        $provider = Saml::getInstance()->getProvider()->findByIssuer(
            $issuer
        );
        if(!$provider) {
            throw new InvalidIssuer(
                sprintf("Invalid issuer: %s", $issuer->getValue())
            );
        }
        return $provider;
    }
}
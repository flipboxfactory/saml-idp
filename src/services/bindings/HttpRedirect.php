<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/11/18
 * Time: 9:44 PM
 */

namespace flipbox\saml\idp\services\bindings;


use flipbox\saml\core\exceptions\InvalidIssuer;
use flipbox\saml\core\services\bindings\AbstractHttpRedirect;
use flipbox\saml\sp\Saml;
use LightSaml\Model\Assertion\Issuer;
use flipbox\saml\core\records\ProviderInterface;

class HttpRedirect extends AbstractHttpRedirect
{
    /**
     * @inheritdoc
     */
    public function getProviderByIssuer(Issuer $issuer): ProviderInterface
    {
        $provider = Saml::getInstance()->getProvider()->findByEntityId(
            $issuer->getValue()
        );
        if(!$provider) {
            throw new InvalidIssuer(
                sprintf("Invalid issuer: %s", $issuer->getValue())
            );
        }
        return $provider;
    }
}
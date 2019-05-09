<?php

namespace flipbox\saml\idp\services;

use flipbox\saml\core\services\AbstractProviderService;
use flipbox\saml\core\services\ProviderServiceInterface;
use flipbox\saml\idp\Saml;
use flipbox\saml\idp\traits\SamlPluginEnsured;

/**
 * Class Provider
 * @package flipbox\saml\idp\services
 */
class Provider extends AbstractProviderService implements ProviderServiceInterface
{
    use SamlPluginEnsured;

    /**
     * @inheritdoc
     */
    public function findOwn()
    {
        return $this->findByEntityId(Saml::getInstance()->getSettings()->getEntityId())->one();
    }
}

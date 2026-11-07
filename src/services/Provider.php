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
     * @deprecated
     */
    public function findOwn()
    {
        // saml-core documents findByEntityId() as yii\db\Query, but it returns an ActiveQuery of ProviderRecord
        // @phpstan-ignore return.type
        return $this->findByEntityId(Saml::getInstance()->getSettings()->getEntityId())->one();
    }
}

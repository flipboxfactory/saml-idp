<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 10:44 PM
 */

namespace flipbox\saml\idp\services;


use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\core\services\AbstractProviderService;
use flipbox\saml\core\services\ProviderServiceInterface;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\Saml;

/**
 * Class Provider
 * @package flipbox\saml\idp\services
 */
class Provider extends AbstractProviderService implements ProviderServiceInterface
{
    /**
     * @inheritdoc
     */
    public function getRecordClass()
    {
        return ProviderRecord::class;
    }

    /**
     * @inheritdoc
     */
    public function findOwn(): AbstractProvider
    {
        return $this->findByEntityId(Saml::getInstance()->getSettings()->getEntityId());
    }
}
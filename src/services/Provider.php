<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 10:44 PM
 */

namespace flipbox\saml\idp\services;


use flipbox\saml\core\services\messages\AbstractProviderService;
use flipbox\saml\core\services\messages\ProviderServiceInterface;
use flipbox\saml\idp\records\ProviderRecord;

/**
 * Class Provider
 * @package flipbox\saml\idp\services
 */
class Provider extends AbstractProviderService implements ProviderServiceInterface
{
    /**
     * @inheritdoc
     */
    protected function getRecordClass()
    {
        return ProviderRecord::class;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 10:44 PM
 */

namespace flipbox\saml\idp\services;

use flipbox\saml\core\services\AbstractProviderIdentityService;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\traits\SamlPluginEnsured;

/**
 * Class ProviderIdentity
 * @package flipbox\saml\sp\services
 */
class ProviderIdentity extends AbstractProviderIdentityService
{
    use SamlPluginEnsured;

    /**
     * @inheritdoc
     */
    public function getRecordClass()
    {
        return ProviderRecord::class;
    }
}

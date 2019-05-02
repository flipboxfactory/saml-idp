<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/9/18
 * Time: 9:47 AM
 */

namespace flipbox\saml\idp\controllers;


use flipbox\saml\core\controllers\AbstractMetadataController;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\traits\SamlPluginEnsured;

class MetadataController extends AbstractMetadataController
{
    use SamlPluginEnsured;


    protected function getProviderRecord()
    {
        return ProviderRecord::class;
    }
}
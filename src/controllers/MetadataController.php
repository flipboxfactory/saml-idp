<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/9/18
 * Time: 9:47 AM
 */

namespace flipbox\saml\idp\controllers;


use craft\web\Controller;
use flipbox\saml\core\controllers\AbstractMetadataController;
use flipbox\saml\core\helpers\SerializeHelper;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\Saml;

class MetadataController extends AbstractMetadataController
{

    protected function getSamlPlugin(): SamlPluginInterface
    {
        return Saml::getInstance();
    }

    protected function getProviderRecord()
    {
        return ProviderRecord::class;
    }
}
<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\idp\controllers;

use flipbox\saml\core\controllers\AbstractSettingsController;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\traits\SamlPluginEnsured;

/**
 * Class SettingsController
 * @package flipbox\saml\sp\controllers\cp\view
 */
class SettingsController extends AbstractSettingsController
{
    use SamlPluginEnsured;

    /**
     * @inheritdoc
     */
    protected function getProviderRecord()
    {
        return ProviderRecord::class;
    }
}

<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\idp\controllers\cp\view\metadata;

use flipbox\saml\core\controllers\cp\view\metadata\AbstractDefaultController;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\traits\SamlPluginEnsured;

class DefaultController extends AbstractDefaultController
{
    use SamlPluginEnsured;

    /**
     * @inheritdoc
     */
    public function getProviderRecord()
    {
        return ProviderRecord::class;
    }
}

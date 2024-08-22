<?php

namespace flipbox\saml\idp\controllers\cp\view\metadata;

use flipbox\saml\core\controllers\cp\view\metadata\AbstractEditController;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\traits\SamlPluginEnsured;

class EditController extends AbstractEditController
{
    use SamlPluginEnsured;

    /**
     * @return string
     */
    protected function getProviderRecord()
    {
        return ProviderRecord::class;
    }
}

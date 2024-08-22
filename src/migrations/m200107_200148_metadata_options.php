<?php

namespace flipbox\saml\idp\migrations;

use flipbox\saml\idp\records\ProviderRecord;

class m200107_200148_metadata_options extends \flipbox\saml\core\migrations\m200107_200148_metadata_options
{
    protected static function getProviderTableName()
    {
        return ProviderRecord::tableName();
    }
}

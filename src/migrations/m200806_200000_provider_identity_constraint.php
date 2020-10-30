<?php

namespace flipbox\saml\idp\migrations;

use flipbox\saml\core\migrations\m200806_200000_provider_identity_constraint as abstractMigration;
use flipbox\saml\idp\records\ProviderIdentityRecord;
use flipbox\saml\idp\Saml;

/**
 */
class m200806_200000_provider_identity_constraint extends abstractMigration
{

    protected static function getIdentityTableName()
    {
        return ProviderIdentityRecord::tableName();
    }

    public function safeUp()
    {
        try {
            // might not need this ... move on, on exception
            parent::safeUp();
        } catch (\Exception $e) {
            Saml::warning($e->getMessage() . ' -> ' . $e->getTraceAsString());
        }
    }
}

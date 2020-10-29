<?php

namespace flipbox\saml\idp\migrations;

use flipbox\saml\core\migrations\m200806_200000_provider_identity_constraint as abstractMigration;
use flipbox\saml\idp\records\ProviderIdentityRecord;

/**
 */
class m200806_200000_provider_identity_constraint extends abstractMigration
{

    protected static function getIdentityTableName() {
        return ProviderIdentityRecord::tableName();
    }
}

<?php

namespace flipbox\saml\idp\migrations;

use craft\db\Migration;
use craft\records\User as UserRecord;
use flipbox\keychain\records\KeyChainRecord;
use flipbox\keychain\traits\MigrateKeyChain;
use flipbox\saml\core\migrations\AbstractInstall;
use flipbox\saml\idp\records\ProviderIdentityRecord;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\Saml;
use yii\base\Module;


/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Install extends AbstractInstall
{
    use MigrateKeyChain;

    /**
     * @inheritdoc
     */
    protected function getModule(): Module
    {
        return Saml::getInstance();
    }

    protected function getProviderFields()
    {
        return array_merge(
            parent::getProviderFields(),
            [
                'encryptAssertions' => $this->boolean()->defaultValue(false)->notNull(),//->after(static::PROVIDER_AFTER_COLUMN),
                'signResponse'      => $this->boolean()->defaultValue(true)->notNull(),//->after(static::PROVIDER_AFTER_COLUMN),
                'useCpLogin'        => $this->boolean()->defaultValue(true)->notNull(),//->after(static::PROVIDER_AFTER_COLUMN),
            ]
        );
    }

    /**
     * @inheritdoc
     */
    protected function getProviderTableName()
    {
        return ProviderRecord::tableName();
    }

    /**
     * @inheritdoc
     */
    protected function getIdentityTableName()
    {
        return ProviderIdentityRecord::tableName();
    }
}

<?php

namespace flipbox\saml\idp\migrations;

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
                'encryptAssertions' => $this->boolean()->defaultValue(true)->notNull(),
            ]
        );
    }

    /**
     * @inheritdoc
     */
    protected function getProviderTableName(): string
    {
        return ProviderRecord::tableName();
    }

    /**
     * @inheritdoc
     */
    protected function getIdentityTableName(): string
    {
        return ProviderIdentityRecord::tableName();
    }
}

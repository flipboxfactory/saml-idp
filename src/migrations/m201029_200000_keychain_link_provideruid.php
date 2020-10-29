<?php

namespace flipbox\saml\idp\migrations;

use craft\db\ActiveQuery;
use flipbox\saml\idp\records\ProviderRecord;

/**
 */
class m201029_200000_keychain_link_provideruid extends
    \flipbox\saml\core\migrations\m201029_200000_keychain_link_provideruid
{

    /**
     * @return ActiveQuery
     */
    protected function providerRecordQuery() {
        return ProviderRecord::find();
    }
}

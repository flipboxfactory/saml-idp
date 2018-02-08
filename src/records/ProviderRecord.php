<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 9:33 PM
 */

namespace flipbox\saml\idp\records;


use flipbox\ember\records\traits\StateAttribute;
use flipbox\saml\core\records\AbstractProviderRecord;

class ProviderRecord extends AbstractProviderRecord
{

    use StateAttribute;

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'saml_idp_providers';

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeyPairs()
    {
        return $this->hasMany(KeyPairRecord::class,['key_id', 'id']);
    }
}
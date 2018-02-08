<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 9:33 PM
 */

namespace flipbox\saml\idp\records;


use flipbox\ember\records\ActiveRecord;
use flipbox\ember\helpers\ModelHelper;

class ProviderIdentityRecord extends ActiveRecord
{

    const TABLE_ALIAS = 'saml_idp_provider_identity';

}
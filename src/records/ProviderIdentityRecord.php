<?php

namespace flipbox\saml\idp\records;

use flipbox\ember\records\traits\StateAttribute;
use flipbox\saml\core\records\AbstractProviderIdentity;

class ProviderIdentityRecord extends AbstractProviderIdentity
{

    use StateAttribute;

    const TABLE_ALIAS = 'saml_idp_provider_identity';

    /**
     * @inheritdoc
     */
    public function getProvider()
    {
        return $this->hasOne(
            ProviderRecord::class,
            [
                'providerId' => 'id',
            ]
        );
    }
}

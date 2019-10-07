<?php

namespace flipbox\saml\idp\records;

use flipbox\saml\core\records\AbstractProviderIdentity;

class ProviderIdentityRecord extends AbstractProviderIdentity
{

    const TABLE_ALIAS = 'saml_idp_provider_identity';

    /**
     * @inheritdoc
     */
    public function getProvider()
    {
        return $this->hasOne(
            ProviderRecord::class,
            [
                'id' => 'providerId',
            ]
        );
    }
}

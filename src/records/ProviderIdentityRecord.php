<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 9:33 PM
 */

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
    public function rules()
    {
        return array_merge(parent::rules(), [
            [
                [
                    'lastLoginDate',
                    'sessionId',
                ],
                'safe',
                'on' => [
                    ModelHelper::SCENARIO_DEFAULT
                ]
            ]
        ]);
    }

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
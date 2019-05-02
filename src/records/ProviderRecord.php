<?php

namespace flipbox\saml\idp\records;


use flipbox\ember\records\traits\StateAttribute;
use flipbox\saml\core\models\SettingsInterface;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\idp\Saml;

/**
 * Class ProviderRecord
 * @package flipbox\saml\idp\records
 * @property boolean $useCpLogin
 * @property boolean $encryptAssertions
 */
class ProviderRecord extends AbstractProvider implements ProviderInterface
{

    use StateAttribute;

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'saml_idp_providers';

    /**
     * @inheritdoc
     */
    public function getLoginPath()
    {
        if ($this->type !== SettingsInterface::SP) {
            return null;
        }
        return implode(
            DIRECTORY_SEPARATOR,
            [
                Saml::getInstance()->getSettings()->loginRequestEndpoint,
                $this->uid,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getLogoutPath()
    {
        if ($this->type !== SettingsInterface::SP) {
            return null;
        }
        return implode(
            DIRECTORY_SEPARATOR,
            [
                Saml::getInstance()->getSettings()->logoutRequestEndpoint,
                $this->uid,
            ]
        );
    }
}

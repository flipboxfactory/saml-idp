<?php

namespace flipbox\saml\idp\records;

use flipbox\saml\core\helpers\UrlHelper;
use flipbox\saml\core\models\AbstractSettings;
use flipbox\saml\core\models\SettingsInterface;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\idp\Saml;

/**
 * Class ProviderRecord
 * @package flipbox\saml\idp\records
 * @property boolean $encryptAssertions
 */
class ProviderRecord extends AbstractProvider implements ProviderInterface
{
    /**
     * The table alias
     */
    public const TABLE_ALIAS = 'saml_idp_providers';

    /**
     * @return AbstractSettings
     */
    protected function getDefaultSettings(): AbstractSettings
    {
        return Saml::getInstance()->getSettings();
    }

    /**
     * @inheritdoc
     */
    public function getLoginPath()
    {
        if ($this->providerType !== SettingsInterface::SP) {
            return null;
        }
        return UrlHelper::buildEndpointPath(
            Saml::getInstance()->getSettings(),
            UrlHelper::LOGIN_ENDPOINT
        );
    }

    /**
     * @inheritdoc
     */
    public function getLogoutPath()
    {
        if ($this->providerType !== SettingsInterface::SP) {
            return null;
        }
        return UrlHelper::buildEndpointPath(
            Saml::getInstance()->getSettings(),
            UrlHelper::LOGOUT_ENDPOINT
        );
    }
}

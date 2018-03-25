<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/9/18
 * Time: 9:48 AM
 */

namespace flipbox\saml\idp\services\messages;


use craft\base\Component;
use craft\helpers\UrlHelper;
use flipbox\keychain\records\KeyChainRecord;
use flipbox\saml\core\events\RegisterTransformer;
use flipbox\saml\core\exceptions\InvalidMetadata;
use flipbox\saml\core\helpers\SerializeHelper;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\core\services\messages\AbstractMetadata;
use flipbox\saml\core\services\messages\MetadataServiceInterface;
use flipbox\saml\core\services\traits\Metadata as MetadataTrait;
use flipbox\saml\idp\records\ProviderRecord;
use Flipbox\Transform\Factory;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Metadata\IdpSsoDescriptor;
use LightSaml\Model\Metadata\SingleLogoutService;
use LightSaml\Model\Metadata\SingleSignOnService;
use LightSaml\SamlConstants;
use flipbox\saml\idp\Saml;

class Metadata extends AbstractMetadata implements MetadataServiceInterface
{
    use MetadataTrait;

    /**
     *
     */
    const LOGIN_LOCATION = 'saml-idp/login/request';
    const LOGOUT_RESPONSE_LOCATION = 'saml-idp/logout/response';
    const LOGOUT_REQUEST_LOCATION = 'saml-idp/logout/request';

    /**
     * @return array
     */
    public function getSupportedBindings()
    {
        return $this->supportedBindings;
    }

    /**
     * @param AbstractProvider $provider
     * @return bool
     */
    protected function useEncryption(AbstractProvider $provider)
    {
        return Saml::getInstance()->getSettings()->encryptAssertions;
    }

    /**
     * @param AbstractProvider $provider
     * @return bool
     */
    protected function useSigning(AbstractProvider $provider)
    {
        return Saml::getInstance()->getSettings()->signAuthnRequest;
    }

    /**
     * Utils
     */

    /**
     * @inheritdoc
     */
    protected function getSamlPlugin(): SamlPluginInterface
    {
        return Saml::getInstance();
    }
}

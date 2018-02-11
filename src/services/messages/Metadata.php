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
use flipbox\saml\core\exceptions\InvalidMetadata;
use flipbox\saml\core\helpers\SerializeHelper;
use flipbox\saml\core\records\AbstractProvider;
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\core\services\messages\MetadataServiceInterface;
use flipbox\saml\core\services\traits\Metadata as MetadataTrait;
use flipbox\saml\idp\records\ProviderRecord;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Metadata\IdpSsoDescriptor;
use LightSaml\Model\Metadata\SingleLogoutService;
use LightSaml\Model\Metadata\SingleSignOnService;
use LightSaml\Model\Metadata\SSODescriptor;
use LightSaml\SamlConstants;
use flipbox\saml\idp\Saml;

class Metadata extends Component implements MetadataServiceInterface
{
    use MetadataTrait;

    /**
     *
     */
    const LOGIN_LOCATION = 'saml-idp/login/request';
    const LOGOUT_RESPONSE_LOCATION = 'saml-idp/logout/response';
    const LOGOUT_REQUEST_LOCATION = 'saml-idp/logout/request';


    /**
     * @return string
     */
    public static function getLogoutResponseLocation()
    {
        return UrlHelper::actionUrl(static::LOGOUT_RESPONSE_LOCATION);
    }

    /**
     * @return string
     */
    public static function getLogoutRequestLocation()
    {
        return UrlHelper::actionUrl(static::LOGOUT_REQUEST_LOCATION);
    }

    /**
     * @return string
     */
    public static function getLoginLocation()
    {
        return UrlHelper::actionUrl(static::LOGIN_LOCATION);
    }

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
        return $provider->encryptAssertions;
    }

    /**
     * @param AbstractProvider $provider
     * @return bool
     */
    protected function useSigning(AbstractProvider $provider)
    {
        return $provider->signResponse;
    }

    /**
     * @param KeyChainRecord|null $withKeyPair
     * @param bool $createKeyFromSettings
     * @return ProviderInterface
     * @throws InvalidMetadata
     */
    public function create(KeyChainRecord $withKeyPair = null, $createKeyFromSettings = false): ProviderInterface
    {
        /** @var IdpSsoDescriptor $idpRedirectDescriptor */
        $idpRedirectDescriptor = $this->createRedirectDescriptor()
            ->addSingleSignOnService(
                new SingleSignOnService(
                    static::getLoginLocation(),
                    SamlConstants::BINDING_SAML2_HTTP_REDIRECT
                )
            )
            ->addSingleLogoutService(
                (new SingleLogoutService())
                    ->setLocation(static::getLogoutResponseLocation())
                    ->setResponseLocation(static::getLogoutResponseLocation())
                    ->setBinding(SamlConstants::BINDING_SAML2_HTTP_REDIRECT)
            );
        /** @var IdpSsoDescriptor $idpPostDescriptor */
        $idpPostDescriptor = $this->createPostDescriptor()
            ->addSingleSignOnService(
                new SingleSignOnService(
                    static::getLoginLocation(),
                    SamlConstants::BINDING_SAML2_HTTP_POST
                )
            )
            ->addSingleLogoutService(
                (new SingleLogoutService())
                    ->setLocation(static::getLogoutRequestLocation())
                    ->setResponseLocation(static::getLogoutResponseLocation())
                    ->setBinding(SamlConstants::BINDING_SAML2_HTTP_POST)
            );

        $entityDescriptor = new EntityDescriptor(
            Saml::getInstance()->getSettings()->getEntityId(),
            [
                $idpRedirectDescriptor,
                $idpPostDescriptor,
            ]);

        $provider = (new ProviderRecord())
            ->loadDefaultValues();

        /**
         * Load Defaults to know what to do with
         */
        $provider->loadDefaultValues();

        if ($withKeyPair) {
            if ($this->useEncryption($provider)) {
                $this->setEncrypt($idpRedirectDescriptor, $withKeyPair);
                $this->setEncrypt($idpPostDescriptor, $withKeyPair);
            }
            if ($this->useSigning($provider)) {
                $this->setSign($idpRedirectDescriptor, $withKeyPair);
                $this->setSign($idpPostDescriptor, $withKeyPair);
            }
        }

        \Craft::configure($provider, [
            'entityId' => $entityDescriptor->getEntityID(),
            'metadata' => SerializeHelper::toXml($entityDescriptor),
        ]);


        if (! $this->saveProvider($provider)) {
            throw new \Exception($provider->getFirstError());
        }

        return $provider;
    }

    /**
     * @return IdpSsoDescriptor
     * @throws InvalidMetadata
     */
    public function createRedirectDescriptor()
    {
        return $this->createDescriptor(SamlConstants::BINDING_SAML2_HTTP_REDIRECT);
    }

    /**
     * @return IdpSsoDescriptor
     * @throws InvalidMetadata
     */
    public function createPostDescriptor()
    {
        return $this->createDescriptor(SamlConstants::BINDING_SAML2_HTTP_POST);
    }

    /**
     * @param $binding
     * @return IdpSsoDescriptor
     * @throws \flipbox\saml\core\exceptions\InvalidMetadata
     */
    public function createDescriptor($binding)
    {
        if (! in_array($binding, $this->getSupportedBindings())) {
            throw new InvalidMetadata(
                sprintf("Binding is not supported: %s", $binding)
            );
        }

        $idpDescriptor = new IdpSsoDescriptor();

        $idpDescriptor->setWantAuthnRequestsSigned(
            Saml::getInstance()->getSettings()->signAuthnRequest
        );


        return $idpDescriptor;

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

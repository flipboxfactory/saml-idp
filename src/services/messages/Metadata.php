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
use flipbox\saml\core\records\ProviderInterface;
use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\core\services\messages\MetadataServiceInterface;
use flipbox\saml\core\services\traits\Metadata as MetadataTrait;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Metadata\IdpSsoDescriptor;
use LightSaml\Model\Metadata\SingleLogoutService;
use LightSaml\Model\Metadata\SingleSignOnService;
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
     * @var array
     */
    protected $supportedBindings = [
        SamlConstants::BINDING_SAML2_HTTP_REDIRECT,
        SamlConstants::BINDING_SAML2_HTTP_POST,
    ];

    /**
     * @return array
     */
    public function getSupportedBindings()
    {
        return $this->supportedBindings;
    }

    /**
     * @param KeyChainRecord|null $withKeyPair
     * @param bool $createKeyFromSettings
     * @return ProviderInterface
     * @throws InvalidMetadata
     */
    public function create(KeyChainRecord $withKeyPair = null, $createKeyFromSettings = false) : ProviderInterface
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

        return $entityDescriptor;
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

<?php

namespace flipbox\saml\idp\tests\unit\coverage;

use Codeception\Scenario;
use craft\elements\User;
use craft\models\UserGroup;
use flipbox\saml\core\models\GroupOptions;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\Saml;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\DOMDocumentFactory;
use SAML2\Response as SamlResponse;
use Step\Unit\Common\AuthnRequest as AuthnRequestFactory;
use Step\Unit\Common\Metadata;

/**
 * Shared setup for IdP response/assertion tests.
 */
trait IdpFixtures
{
    protected Saml $module;

    protected Metadata $metadata;

    protected AuthnRequestFactory $authnRequests;

    /** ACS location in tests/_data/xml/sp-metadata-*.xml */
    protected const SP_ACS = 'http://sp.localhost:9090/sso/login';

    /** entityID in tests/_data/xml/sp-metadata-*.xml */
    protected const SP_ENTITY_ID = 'http://sp.localhost:9090/';

    protected function setUpIdp(): void
    {
        $this->module = Saml::getInstance() ?? new Saml('saml-idp');
        $this->module->loadSaml2Container();

        $scenario = new Scenario($this);
        $this->metadata = new Metadata($this->module, $scenario);
        $this->authnRequests = new AuthnRequestFactory($this->module, $this->metadata, $scenario);
    }

    /**
     * Remote SP from tests/_data/xml/sp-metadata-with-keys.xml (signing + encryption keys = saml-sp.crt).
     */
    protected function serviceProvider(array $config = []): ProviderRecord
    {
        /** @var ProviderRecord $sp */
        $sp = $this->metadata->createTheirProviderWithSigningKey($this->module);
        $sp->syncGroups = false;
        $sp->encryptAssertions = false;
        $sp->groupsAttributeName = 'groups';
        $sp->setGroupOptions(new GroupOptions(['options' => ['allow' => [GroupOptions::ALLOW_ANY]]]));

        \Craft::configure($sp, $config);

        return $sp;
    }

    /**
     * This IdP, with a freshly generated key pair.
     */
    protected function identityProvider(): ProviderRecord
    {
        return $this->metadata->createMyProviderWithKey($this->module);
    }

    /**
     * @param array<int, string> $groups id => handle
     */
    protected function user(array $groups = []): User
    {
        $userGroups = [];
        foreach ($groups as $id => $handle) {
            $userGroups[] = new UserGroup(['id' => $id, 'name' => ucfirst($handle), 'handle' => $handle]);
        }

        return new User([
            'id' => 1,
            'username' => 'jane.doe',
            'email' => 'jane@example.com',
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'groups' => $userGroups,
        ]);
    }

    /**
     * Serializes (and therefore signs/encrypts) the Response, then parses it back as an SP would receive it.
     */
    protected function roundTrip(SamlResponse $response): SamlResponse
    {
        $xml = $response->toSignedXML();

        return new SamlResponse(DOMDocumentFactory::fromString($xml->ownerDocument->saveXML($xml))->documentElement);
    }

    protected function idpPublicKey(ProviderRecord $identityProvider): XMLSecurityKey
    {
        $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
        $key->loadKey($identityProvider->keychain->getDecryptedCertificate(), false, true);

        return $key;
    }

    /**
     * Private key matching the SP encryption certificate in sp-metadata-with-keys.xml.
     */
    protected function spDecryptionKey(): XMLSecurityKey
    {
        $key = new XMLSecurityKey(XMLSecurityKey::RSA_OAEP_MGF1P, ['type' => 'private']);
        $key->loadKey(codecept_data_dir() . '/keypairs/saml-sp.pem', true);

        return $key;
    }
}

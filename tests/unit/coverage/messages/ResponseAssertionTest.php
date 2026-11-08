<?php

namespace flipbox\saml\idp\tests\unit\coverage\messages;

use Codeception\Test\Unit;
use flipbox\saml\core\exceptions\AccessDenied;
use flipbox\saml\core\models\GroupOptions;
use flipbox\saml\idp\tests\unit\coverage\IdpFixtures;
use SAML2\Assertion;
use SAML2\Constants;
use SAML2\EncryptedAssertion;
use yii\base\InvalidConfigException;

class ResponseAssertionTest extends Unit
{
    use IdpFixtures;

    protected function _before(): void
    {
        $this->setUpIdp();
    }

    public function testResponseAndAssertionAreAddressedToTheServiceProvider(): void
    {
        $sp = $this->serviceProvider();
        $idp = $this->identityProvider();
        $before = time();

        $response = $this->module->getResponse()->create($this->user(), $idp, $sp, $this->module->getSettings());

        $this->assertSame(self::SP_ACS, $response->getDestination());
        $this->assertSame($idp->getEntityId(), $response->getIssuer()->getValue());
        $this->assertSame(Constants::STATUS_SUCCESS, $response->getStatus()['Code']);

        /** @var Assertion $assertion */
        $assertion = $response->getAssertions()[0];
        $this->assertInstanceOf(Assertion::class, $assertion);
        $this->assertSame([self::SP_ENTITY_ID], $assertion->getValidAudiences());
        $this->assertSame('jane.doe', $assertion->getNameId()->getValue());

        $confirmation = $assertion->getSubjectConfirmation()[0];
        $this->assertSame(Constants::CM_BEARER, $confirmation->getMethod());
        $this->assertSame(self::SP_ACS, $confirmation->getSubjectConfirmationData()->getRecipient());
        $this->assertNull($confirmation->getSubjectConfirmationData()->getInResponseTo());
        $this->assertSame($this->module->getSettings()->getEntityId(), $confirmation->getNameId()->getNameQualifier());

        // Default window: valid from now for 10 minutes
        $this->assertEqualsWithDelta($before, $assertion->getNotBefore(), 5);
        $this->assertEqualsWithDelta($before + 600, $assertion->getNotOnOrAfter(), 5);
        $this->assertEqualsWithDelta($before + 600, $confirmation->getSubjectConfirmationData()->getNotOnOrAfter(), 5);
        $this->assertSame(Constants::AC_PASSWORD_PROTECTED_TRANSPORT, $assertion->getAuthnContextClassRef());
        $this->assertGreaterThan($before, $assertion->getSessionNotOnOrAfter());
    }

    public function testResponseToAnAuthnRequestCarriesItsId(): void
    {
        $authnRequest = $this->authnRequests->createAuthnRequest();
        $authnRequest->setAssertionConsumerServiceURL(self::SP_ACS);
        $authnRequest->setRelayState('relay-123');

        $response = $this->module->getResponse()->create(
            $this->user(),
            $this->identityProvider(),
            $this->serviceProvider(),
            $this->module->getSettings(),
            $authnRequest
        );
        $this->module->getResponse()->finalizeWithAuthnRequest($response, $authnRequest);

        $data = $response->getAssertions()[0]->getSubjectConfirmation()[0]->getSubjectConfirmationData();
        $this->assertSame($authnRequest->getId(), $data->getInResponseTo());
        $this->assertSame(self::SP_ACS, $data->getRecipient());
        $this->assertSame($authnRequest->getId(), $response->getInResponseTo());
        $this->assertSame('relay-123', $response->getRelayState());
    }

    public function testResponseIsSignedWithTheIdentityProviderKey(): void
    {
        $idp = $this->identityProvider();
        $response = $this->module->getResponse()->create($this->user(), $idp, $this->serviceProvider(), $this->module->getSettings());

        $received = $this->roundTrip($response);

        $this->assertTrue($received->validate($this->idpPublicKey($idp)));
        // A different key must not verify
        $this->expectException(\Exception::class);
        $received->validate($this->idpPublicKey($this->identityProvider()));
    }

    public function testAssertionIsSignedWhenTheServiceProviderWantsSignedAssertions(): void
    {
        $sp = $this->serviceProvider();
        $sp->spSsoDescriptors()[0]->setWantAssertionsSigned(true);
        $idp = $this->identityProvider();

        $response = $this->module->getResponse()->create($this->user(), $idp, $sp, $this->module->getSettings());
        $assertion = $this->roundTrip($response)->getAssertions()[0];

        $this->assertInstanceOf(Assertion::class, $assertion);
        $this->assertTrue($assertion->validate($this->idpPublicKey($idp)));
        $this->assertSame([$idp->keychain->getDecryptedCertificate()], $response->getAssertions()[0]->getCertificates());
    }

    public function testAssertionIsUnsignedWhenNotRequested(): void
    {
        $response = $this->module->getResponse()->create(
            $this->user(),
            $this->identityProvider(),
            $this->serviceProvider(),
            $this->module->getSettings()
        );

        $this->assertFalse($this->roundTrip($response)->getAssertions()[0]->validate($this->idpPublicKey($this->identityProvider())));
    }

    public function testAssertionIsEncryptedToTheServiceProviderKey(): void
    {
        $sp = $this->serviceProvider(['encryptAssertions' => true]);

        $response = $this->module->getResponse()->create($this->user(), $this->identityProvider(), $sp, $this->module->getSettings());
        $encrypted = $this->roundTrip($response)->getAssertions()[0];

        $this->assertInstanceOf(EncryptedAssertion::class, $encrypted);
        $assertion = $encrypted->getAssertion($this->spDecryptionKey());
        $this->assertSame([self::SP_ENTITY_ID], $assertion->getValidAudiences());
        $this->assertSame('jane.doe', $assertion->getNameId()->getValue());
    }

    public function testEncryptedAssertionCannotBeDecryptedWithAnotherKey(): void
    {
        $sp = $this->serviceProvider(['encryptAssertions' => true]);
        $response = $this->module->getResponse()->create($this->user(), $this->identityProvider(), $sp, $this->module->getSettings());
        $encrypted = $this->roundTrip($response)->getAssertions()[0];

        $otherKey = new \RobRichards\XMLSecLibs\XMLSecurityKey(
            \RobRichards\XMLSecLibs\XMLSecurityKey::RSA_OAEP_MGF1P,
            ['type' => 'private']
        );
        openssl_pkey_export(openssl_pkey_new(['private_key_bits' => 2048]), $pem);
        $otherKey->loadKey($pem);

        $this->expectException(\Exception::class);
        $encrypted->getAssertion($otherKey);
    }

    public function testEncryptionWithoutAnEncryptionKeyFails(): void
    {
        // SP metadata without any KeyDescriptor
        $sp = $this->metadata->createTheirProvider($this->module);
        $sp->encryptAssertions = true;
        $sp->syncGroups = false;
        $sp->setGroupOptions(new GroupOptions(['options' => ['allow' => [GroupOptions::ALLOW_ANY]]]));

        $this->expectExceptionMessage('No encryption key found for the service provider.');
        $this->module->getResponse()->create($this->user(), $this->identityProvider(), $sp, $this->module->getSettings());
    }

    public function testOnlyMappedAttributesAreReleased(): void
    {
        // Fixture mapping: att1 = email, att2 = firstName, att3 = "{lastName}" template
        $response = $this->module->getResponse()->create(
            $this->user([1 => 'editors']),
            $this->identityProvider(),
            $this->serviceProvider(),
            $this->module->getSettings()
        );

        $attributes = $this->roundTrip($response)->getAssertions()[0]->getAttributes();

        $this->assertSame(['att1', 'att2', 'att3'], array_keys($attributes));
        $this->assertSame(['jane@example.com'], array_values($attributes['att1']));
        $this->assertSame(['Jane'], array_values($attributes['att2']));
        $this->assertSame(['Doe'], array_values($attributes['att3']));
    }

    public function testDefaultAttributeMapIsReleasedWhenTheProviderHasNoMapping(): void
    {
        // e.g. a provider record created outside the control panel
        $sp = $this->serviceProvider(['mapping' => null]);

        $response = $this->module->getResponse()->create($this->user(), $this->identityProvider(), $sp, $this->module->getSettings());
        $attributes = $this->roundTrip($response)->getAssertions()[0]->getAttributes();

        $this->assertSame(['Email', 'FirstName', 'LastName'], array_keys($attributes));
        $this->assertSame(['jane@example.com'], array_values($attributes['Email']));
        $this->assertSame(['Jane'], array_values($attributes['FirstName']));
        $this->assertSame(['Doe'], array_values($attributes['LastName']));
    }

    public function testConfiguredAttributeMapAcceptsPairsAndRows(): void
    {
        $settings = clone $this->module->getSettings();
        $settings->responseAttributeMap = [
            'mail' => 'email',
            ['attributeName' => 'displayName', 'templateOverride' => '{firstName} {lastName}'],
        ];

        $response = $this->module->getResponse()->create(
            $this->user(),
            $this->identityProvider(),
            $this->serviceProvider(['mapping' => null]),
            $settings
        );
        $attributes = $this->roundTrip($response)->getAssertions()[0]->getAttributes();

        $this->assertSame(['mail', 'displayName'], array_keys($attributes));
        $this->assertSame(['jane@example.com'], array_values($attributes['mail']));
        $this->assertSame(['Jane Doe'], array_values($attributes['displayName']));
    }

    public function testEmptyProviderMappingReleasesNoAttributes(): void
    {
        // Saved from the control panel with no rows: the default map must not apply
        $sp = $this->serviceProvider(['mapping' => '[]']);

        $response = $this->module->getResponse()->create($this->user(), $this->identityProvider(), $sp, $this->module->getSettings());

        $this->assertSame([], $this->roundTrip($response)->getAssertions()[0]->getAttributes());
    }

    public function testInvalidAttributeMapEntryIsRejected(): void
    {
        $settings = clone $this->module->getSettings();
        $settings->responseAttributeMap = ['Email' => fn() => 'jane@example.com'];

        $this->expectException(InvalidConfigException::class);
        $this->module->getResponse()->create(
            $this->user(),
            $this->identityProvider(),
            $this->serviceProvider(['mapping' => null]),
            $settings
        );
    }

    public function testDateAttributesAreIso8601(): void
    {
        $sp = $this->serviceProvider([
            'mapping' => json_encode([['attributeName' => 'created', 'craftProperty' => 'dateCreated']]),
        ]);
        $user = $this->user();
        $user->dateCreated = new \DateTime('2026-01-02 03:04:05', new \DateTimeZone('UTC'));

        $response = $this->module->getResponse()->create($user, $this->identityProvider(), $sp, $this->module->getSettings());
        $attributes = $response->getAssertions()[0]->getAttributes();

        $this->assertSame(['created' => '2026-01-02T03:04:05+0000'], $attributes['created']);
    }

    public function testOnlySyncableGroupsAreReleased(): void
    {
        $sp = $this->serviceProvider(['syncGroups' => true]);
        $sp->setGroupOptions(new GroupOptions(['options' => [
            'allow' => [GroupOptions::ALLOW_ANY],
            'sync' => [1],
        ]]));

        $response = $this->module->getResponse()->create(
            $this->user([1 => 'editors', 2 => 'admins']),
            $this->identityProvider(),
            $sp,
            $this->module->getSettings()
        );

        $this->assertSame(['editors'], $response->getAssertions()[0]->getAttributes()['groups']);
    }

    public function testNoGroupsAttributeForUsersWithoutGroups(): void
    {
        $response = $this->module->getResponse()->create(
            $this->user(),
            $this->identityProvider(),
            $this->serviceProvider(['syncGroups' => true]),
            $this->module->getSettings()
        );

        $this->assertArrayNotHasKey('groups', $response->getAssertions()[0]->getAttributes());
    }

    public function testNoGroupsAttributeWhenGroupSyncIsOff(): void
    {
        $response = $this->module->getResponse()->create(
            $this->user([1 => 'editors']),
            $this->identityProvider(),
            $this->serviceProvider(['syncGroups' => false]),
            $this->module->getSettings()
        );

        $this->assertArrayNotHasKey('groups', $response->getAssertions()[0]->getAttributes());
    }

    public function testNameIdOverrideIsUsedForSubjectAndAssertion(): void
    {
        $sp = $this->serviceProvider(['nameIdOverride' => '{email}']);

        $assertion = $this->module->getResponse()
            ->create($this->user(), $this->identityProvider(), $sp, $this->module->getSettings())
            ->getAssertions()[0];

        $this->assertSame('jane@example.com', $assertion->getNameId()->getValue());
        $this->assertSame('jane@example.com', $assertion->getSubjectConfirmation()[0]->getNameId()->getValue());
    }

    public function testUserWithoutGroupsIsAllowedWhenNoGroupIsPermitted(): void
    {
        $sp = $this->serviceProvider();
        $sp->setGroupOptions(new GroupOptions(['options' => ['allow' => [GroupOptions::NO_GROUP]]]));

        $response = $this->module->getResponse()->create($this->user(), $this->identityProvider(), $sp, $this->module->getSettings());

        $this->assertCount(1, $response->getAssertions());
    }

    public function testUserInAnAllowedGroupIsAllowed(): void
    {
        $sp = $this->serviceProvider();
        $sp->setGroupOptions(new GroupOptions(['options' => ['allow' => [2]]]));

        $response = $this->module->getResponse()->create(
            $this->user([1 => 'editors', 2 => 'members']),
            $this->identityProvider(),
            $sp,
            $this->module->getSettings()
        );

        $this->assertCount(1, $response->getAssertions());
    }

    public function testUserWithoutGroupsIsDeniedWhenGroupsAreRequired(): void
    {
        $sp = $this->serviceProvider();
        $sp->setGroupOptions(new GroupOptions(['options' => ['allow' => [2]]]));

        $this->expectException(AccessDenied::class);
        $this->module->getResponse()->create($this->user(), $this->identityProvider(), $sp, $this->module->getSettings());
    }
}

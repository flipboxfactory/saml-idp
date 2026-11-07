<?php

namespace tests\unit\idp;

use Codeception\Test\Unit;
use Composer\InstalledVersions;
use Composer\Semver\Comparator;
use Composer\Semver\Semver;

/**
 * 5.0.10 is a security release that works by raising dependency floors. These tests fail if a
 * constraint is loosened enough to admit a vulnerable release again, or if one is installed.
 */
class DependencyFloorTest extends Unit
{
    /**
     * Last vulnerable release of each direct dependency. The composer.json constraint must exclude it.
     */
    private const LAST_VULNERABLE = [
        // saml2 4.19.3 / xmlseclibs 3.1.4 (GHSA-6vhm-xfmv-94pj, GHSA-4v26-v6cg-g6f9, ...)
        'flipboxfactory/saml-core' => '5.0.10',
        // GHSA-wg23-69c2-gjc8, GHSA-p8x7-9vfw-p7vc, ...
        'craftcms/cms' => '5.10.7',
    ];

    /**
     * First fixed release of each transitive SAML dependency.
     */
    private const FIRST_FIXED = [
        'simplesamlphp/saml2' => '4.19.4',
        'robrichards/xmlseclibs' => '3.1.5',
        'flipboxfactory/saml-core' => '5.0.11',
        'craftcms/cms' => '5.10.8',
    ];

    public function testConstraintsExcludeVulnerableReleases(): void
    {
        $require = json_decode(
            (string)file_get_contents(dirname(__DIR__, 3) . '/composer.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        )['require'];

        foreach (self::LAST_VULNERABLE as $package => $version) {
            $this->assertArrayHasKey($package, $require);
            $this->assertFalse(
                Semver::satisfies($version, $require[$package]),
                "composer.json allows $package $version, which has known advisories"
            );
        }
    }

    public function testInstalledReleasesArePatched(): void
    {
        foreach (self::FIRST_FIXED as $package => $minimum) {
            $installed = InstalledVersions::getVersion($package);
            $this->assertNotNull($installed, "$package is not installed");
            $this->assertTrue(
                Comparator::greaterThanOrEqualTo($installed, $minimum),
                "$package $installed is installed; $minimum or later is required"
            );
        }
    }
}

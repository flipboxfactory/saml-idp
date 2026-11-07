# Release Notes for Craft CMS Plugin SAML IDP

## 5.0.11 2026-11-07

### Security
- Requires `flipboxfactory/keychain` ^5.0.3. Earlier KeyChain versions stored the private key of key pairs created with "bring your own key" unencrypted in the `keychain.settings` column, even with "Encrypt at Rest" on, and showed decrypted private keys on the key pair edit page. KeyChain 5.0.3 removes the stored copies on update and hides private keys behind a password-confirmed **Reveal** button. If you used a pasted (BYOK) key pair, consider rotating it: database backups made before the update may contain the plaintext key.

### Changed
- Requires `flipboxfactory/saml-core` ^5.0.12, whose control panel pages use Craft's own markup and styles.

## 5.0.10 2026-11-06

**[CRITICAL] Security release. Update required.** Update Craft CMS to 5.10.8 or later first, then run `composer update flipboxfactory/saml-idp -W`. The `-W` is needed because saml2 4.19.x requires `webmozart/assert` 1.x, while recent Craft projects lock 2.x.

### Fixed
- Requires `flipboxfactory/saml-core` ^5.0.11, which moves `simplesamlphp/saml2` to 4.19.4 and `robrichards/xmlseclibs` to 3.1.5. Fixes:
  - [GHSA-6vhm-xfmv-94pj](https://github.com/simplesamlphp/saml2/security/advisories/GHSA-6vhm-xfmv-94pj) (high): unauthenticated denial of service; a small compressed HTTP-Redirect message could exhaust PHP memory.
  - [GHSA-5cjr-mxj5-wmrx](https://github.com/advisories/GHSA-5cjr-mxj5-wmrx) / CVE-2026-49289 (high): denial of service via XPath Transform.
  - [GHSA-6929-8p9f-26jx](https://github.com/advisories/GHSA-6929-8p9f-26jx) / CVE-2026-49283 (high): HTTP-Artifact cross-IdP authentication bypass.
  - [GHSA-4v26-v6cg-g6f9](https://github.com/advisories/GHSA-4v26-v6cg-g6f9) / CVE-2026-32313 (high): xmlseclibs did not validate the AES-GCM authentication tag on encrypted assertions.
  - [GHSA-c4cc-x928-vjw9](https://github.com/advisories/GHSA-c4cc-x928-vjw9) / CVE-2025-66578 (medium): xmlseclibs canonicalization error could bypass digest/signature validation.
  - saml-core now enforces decryption algorithm blacklists that were previously ignored.
- Requires Craft CMS ^5.10.8, so saml-idp no longer installs alongside Craft releases with published advisories, including:
  - [GHSA-wg23-69c2-gjc8](https://github.com/advisories/GHSA-wg23-69c2-gjc8) (critical): passkey login accepted replayed WebAuthn assertions.
  - [GHSA-p8x7-9vfw-p7vc](https://github.com/advisories/GHSA-p8x7-9vfw-p7vc) (high): arbitrary user password reset leading to admin account takeover.
  - [GHSA-265m-7826-wjqm](https://github.com/advisories/GHSA-265m-7826-wjqm) / CVE-2026-72778 and [GHSA-f5wm-88jv-g5hx](https://github.com/advisories/GHSA-f5wm-88jv-g5hx) / CVE-2026-72781 (high): authenticated remote code execution.
  - [GHSA-v64r-7wg9-23pr](https://github.com/advisories/GHSA-v64r-7wg9-23pr) / CVE-2025-68456 (high): unauthenticated database backup.
  - [GHSA-f3gw-9ww9-jmc3](https://github.com/advisories/GHSA-f3gw-9ww9-jmc3) / CVE-2025-32432 (critical): remote code execution.
- saml-idp 5.0.9 and earlier could resolve to saml-core 5.0.10 with saml2 4.20.x on `composer update`, which fails to install (blocked Composer plugin) or breaks SAML at runtime. 5.0.10 prevents that combination.
- An unauthenticated request to the post-login endpoint returned a 500 instead of 403 Forbidden.
- A session holding a SAML message other than an AuthnRequest is no longer treated as an AuthnRequest.

### Changed
- Requires `flipboxfactory/keychain` ^5.0.1 directly (previously only through saml-core).

### Added
- Test suite with 97% line coverage, coding standards (craftcms/ecs) and static analysis (craftcms/phpstan) in CI
- Merging a version bump to master now tags and publishes the GitHub release automatically
- Tests that fail if the dependency constraints allow, or the install contains, the vulnerable releases above.

### Removed
- The unused control panel IdP login view (`cp/view/LoginController`). It allowed anonymous access and rendered a template that doesn't exist.

## 5.0.9 2026-01-30
### Fixed
- adding more ways to ensure RelayState is saved correctly

## 5.0.8 2026-01-29
### Fixed
- issue with DOMDocument saving to session in some instances

## 5.0.7 2026-01-29
### Fixed
- remove session close

## 5.0.6 2026-01-28
### Fixed
- session adjustments

## 5.0.5 2026-01-28
### Fixed
- issues with sessions

## 5.0.4 2026-01-28
### Fixed
- fixing issues with session with some requests

## 5.0.3 2026-01-27
### Fixed
- issue with import from after login loss of relay state

## 5.0.2 2026-01-27
### Fixed
- issue with after login loss of relay state

## 5.0.1 2024-12-03 [CRITICAL]
### Fixed
- SECURITY PATCH - Update REQUIRED! More info can be found here: https://github.com/simplesamlphp/saml2/security/advisories/GHSA-pxm4-r5ph-q2m2#event-375127

## 5.0.0 2024-08-22
### Added
- Support for Craft 5.0

## 4.1.0 2024-02-10
### Fixed
- Fixing issue with multi-site linking for the external id field

## 4.0.4 2022-12-19
### Fixed
- reverting previous change

## 4.0.3 2022-12-19
### Fixed
- issues with array shifting the keys properly: https://github.com/flipboxfactory/saml-idp/issues/58

## 4.0.1 2022-09-12
### Fixed
- fixing #54: missing recepient on subject confirmation data when idp initiated

## 4.0.1 2022-08-11
### Fixed
- issues with 4.0 typing matching craft parent classes (saml-core)

## 4.0.0 2022-08-01
### Fixed
- Craft support 4.0

## 1.3.7 2021-07-15
### Fixed
- Issue with settings saving from the plugin in the craft control panel (#32)

## 1.3.6 2021-05-14
### Fixed
- Issue with clipboard (using navigator.clipboard with a fallback of the previous method)
- Disallow viewing to settings when allowAdminChanges is false

## 1.3.5 - 2021-05-04
### Fixing
- Issue with mapping preview.

## 1.3.3 - 2021-04-15
### Fixing
- Issue with IdP initiated SSO. Result from 1.3.3 controller changes.

## 1.3.3 - 2021-04-13
### Added
- Ability to be explicit with idp provider when passing a request url.

## 1.3.2 - 2021-02-12
### Fixed
- Fixed mirgration issue with duplicate metadataOptions column

## 1.3.1 - 2021-02-11
### Fixed
- Fixed https://github.com/flipboxfactory/saml-idp/issues/22

## 1.3.0 - 2021-02-11
### Added
- Adding better multisite support

## 1.2.4 - 2020-12-16
### Added
- Updates to CICD. Using github actions! 🚀

## 1.2.3 - 2020-12-14
### Fixed
- Issue with IdP initiated logins

## 1.2.2 - 2020-10-29
### Fixed
- Issue where SP and IdP plugin couldn't be installed on the same craft db due to table conflicts.

## 1.2.0 - 2020-07-14
### Changed
- Updated `simplesamlphp/saml2`

## 1.1.0.2 - 2020-01-08
### Fixed
- Fixing issue with Craft 3.2 twig error within the editableTable

## 1.1.0.1 - 2020-01-08
### Fixed
- Fixing migration issue with craft installs with prefixes.

## 1.1.0 - 2020-01-07
### Fixed
- Fixing issue with requiring admin when project config when `allowAdminChanges` general config is set.
- Duplicate `metadata` html attribute id on the edit page
- Fixed issue with large Metadata too big for the db metadata column (requires migration) https://github.com/flipboxfactory/saml-sp/issues/48

### Added
- Support for Saving Metadata via url (requires migration) https://github.com/flipboxfactory/saml-sp/issues/47

### Changed
- Valid Audience in the Response is now set to the entity id of the destination.

## 1.0.14 - 2019-12-21
### Fixed
- Fixed issue when login is via ajax call. This ends up being a very nice enhancement, cleaning up the login process when a user isn't already logged in!

## 1.0.13 - 2019-12-17
### Fixed
- Fixed issue where the after Response message creation wasn't passing the response to the event correctly.

## 1.0.7 - 2019-10-08
### Added
- Added AudienceRestriction to the Response message.

## 1.0.6 - 2019-10-07
### Removed
- flipboxfactory/craft-ember package to easy updates with dependancies.

## 1.0.5 - 2019-09-26

### Fixed
- Fixed issue with encryption assertions

## 1.0.4 - 2019-09-25

### Fixed
- Fixing more xsd schema compatibility. Changed message ids to be compatible.
- Fixed exception when the user tries to logout (SLO) when they are already logged out.

## 1.0.3 - 2019-09-17

### Fixed
- Adding protocolSupportEnumeration and wantsAuthnRequestsSigned to the metadata

## 1.0.0 - 2019-09-17

### Changed
- Various template updates to specify IDP
### Added
- Environment Variable Support

## 0.0.8 - 2019-07-08

### Fixed
- Fixing RelayState which is no longer manipulated. https://github.com/flipboxfactory/saml-idp/issues/4

### Changed
- Core changes: Removing SLO ResponseLocation for always using the Location. Exchanging metadata for new SLO may be needed.
- Core changes: Allowing SLO to utilize REDIRECT binding

## 0.0.7 - 2019-06-24

### Added
- Starting to use the Changelog!
- Adding allow any to user group access options

### Changed
- Changing the group options access control to allow the user to login if they are
apart of one user group on the allow list.

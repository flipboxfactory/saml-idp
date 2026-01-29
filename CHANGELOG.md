# Release Notes for Craft CMS Plugin SAML IDP

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

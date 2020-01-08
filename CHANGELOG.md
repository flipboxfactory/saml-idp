# Release Notes for Craft CMS Plugin SAML IDP

# 1.1.0.2 - 2020-01-08
### Fixed
- Fixing issue with Craft 3.2 twig error within the editableTable

# 1.1.0.1 - 2020-01-08
### Fixed
- Fixing migration issue with craft installs with prefixes.

# 1.1.0 - 2020-01-07
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

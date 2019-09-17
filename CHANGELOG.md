# Release Notes for Craft CMS Plugin SAML IDP

## 1.0.0 - 2019-09-17

### Changed
- Various template updates to specify IDP
### Added
- Environment Variablel Support

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

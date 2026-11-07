<p align="center"><img src="src/icon.svg" width="96" height="96" alt="SAML Identity Provider icon"></p>

# SAML Identity Provider for Craft CMS

[![CI](https://github.com/flipboxfactory/saml-idp/actions/workflows/ci.yml/badge.svg)](https://github.com/flipboxfactory/saml-idp/actions/workflows/ci.yml)
[![Docs](https://github.com/flipboxfactory/saml-idp/actions/workflows/docs.yml/badge.svg)](https://saml-idp.flipboxfactory.com/)
[![Latest Version](https://img.shields.io/packagist/v/flipboxfactory/saml-idp.svg?style=flat-square)](https://packagist.org/packages/flipboxfactory/saml-idp)
[![Total Downloads](https://img.shields.io/packagist/dt/flipboxfactory/saml-idp.svg?style=flat-square)](https://packagist.org/packages/flipboxfactory/saml-idp)

Turn a Craft CMS site into a SAML 2.0 Identity Provider: your Craft users sign in to other applications (service providers), including other Craft sites running [SAML SP](https://github.com/flipboxfactory/saml-sp).

- Service-provider- and IdP-initiated SSO
- Signed and encrypted responses and assertions
- Per-provider attribute mapping and access control by user group
- Single Logout

**[Read the documentation →](https://saml-idp.flipboxfactory.com/)**

## Requirements

- Craft CMS 5.10.8+
- PHP 8.2+

## Installation

Install from the Craft Plugin Store, or with Composer:

```bash
composer require flipboxfactory/saml-idp
php craft plugin/install saml-idp
```

If Composer reports a `webmozart/assert` conflict, re-run the `composer require` with `-W` (saml2 4.19.x needs `webmozart/assert` 1.x).

## Development

Checks follow Craft's [coding guidelines](https://craftcms.com/docs/5.x/extend/coding-guidelines.html): [craftcms/ecs](https://github.com/craftcms/ecs) and [craftcms/phpstan](https://github.com/craftcms/phpstan). Tests use Codeception with Craft's test module and need a MySQL database:

```bash
docker run -d --rm --name saml-idp-test-mysql -e MYSQL_ROOT_PASSWORD=password \
  -e MYSQL_DATABASE=saml_idp_test -p 127.0.0.1:3306:3306 mysql:8.0
cp tests/.env.example tests/.env   # adjust CRAFT_DB_* if needed

composer check           # check-cs + phpstan + test
composer test-coverage   # needs pcov or Xdebug; CI requires 85% line coverage
composer fix-cs          # auto-fix coding standards
```

CI runs the same checks on every push and pull request. Documentation lives in [`docs/`](docs) and is published from `master`.

## Security

Please report vulnerabilities privately to [support@flipboxfactory.com](mailto:support@flipboxfactory.com) rather than opening a public issue.

## Contributing

See [CONTRIBUTING](CONTRIBUTING.md).

## Credits

- [Flipbox Digital](https://github.com/flipbox)
- [garycrane](https://github.com/garycrane/)

## License

See [LICENSE](LICENSE.md).

# Installation / Upgrading

### Option 1: Composer
Composer is a simple, straight forward installation.  Simply run the following command from your project root:

```bash
composer require flipboxfactory/saml-idp
```

## Upgrading to 5.0.10

5.0.10 is a security release (see the [changelog](https://github.com/flipboxfactory/saml-idp/blob/master/CHANGELOG.md)). It requires Craft CMS 5.10.8 or later, so update Craft first if needed.

saml-idp 5.0.10 uses `simplesamlphp/saml2` 4.19.x, which needs `webmozart/assert` 1.x, while recent Craft projects lock 2.x. If Composer reports a `webmozart/assert` conflict, allow it to adjust dependencies with `-W`:

```bash
composer update flipboxfactory/saml-idp -W
```

# saml-idp

Commercial Craft CMS plugin: SAML 2.0 Identity Provider (Craft users sign in to other applications). Built on `flipboxfactory/saml-core` (shared SAML code, `simplesamlphp/saml2` 4.19.x) and `flipboxfactory/keychain` (key pairs). Changes to those packages only arrive here through a release and a constraint bump in `composer.json`.

## Security first

Customers buy this plugin so their Craft site can vouch for who a user is to other applications. A bug here can hand an attacker a valid identity, even an admin one, on every connected service provider. Security and code quality take priority over speed, convenience and scope.

- **Only issue assertions to registered, enabled service providers,** and only for an authenticated user who passes that provider's access rules (allowed user groups). Fail closed on any doubt.
- **Never trust AuthnRequest values the SP's metadata doesn't back.** Send Responses only to the ACS URL in the SP's metadata, never to an `AssertionConsumerServiceURL` taken from the request. Validate AuthnRequest signatures when they're required.
- **Sign every Response and/or Assertion** with the IdP key, and encrypt when the SP provides an encryption key. Set Audience, Recipient/Destination, InResponseTo and tight NotBefore/NotOnOrAfter windows. Never weaken these, or algorithm choices, to make something work. If a check gets in the way, stop and raise it.
- **Release only the attributes the admin mapped** for that SP. Never add user data, groups or admin status to an assertion by default.
- **Never log or expose secrets**: private keys, signed or decrypted assertions, session or security keys. That includes exceptions, debug logs and CP templates. RelayState and redirect targets must be validated (no open redirects).
- **Dependencies**: run `composer audit` and check GitHub advisories before adding or bumping one, and justify any new dependency.
- **Security fixes** ship as a patch release with a regression test (one that fails without the fix) and a CHANGELOG entry naming the advisory (GHSA/CVE). Handle unreleased vulnerabilities privately (GitHub security advisory), not in public issues, PRs or commit messages.
- When unsure how the SAML spec, saml2 or Craft behaves, verify it against the source or the spec rather than guessing, and say what you couldn't verify.

## Before every commit

```bash
composer check           # ECS + PHPStan + Codeception; must pass
composer fix-cs          # auto-fix style, then re-run check
composer test-coverage   # line coverage must stay >= 85% (CI enforces; needs pcov or Xdebug)
```

Tests need MySQL and `tests/.env` (see README "Development").

## Rules

- Add or update tests in `tests/unit/` for every behaviour change or bug fix. Bug fixes get a test that fails without the fix. Assert outcomes (Response issued or refused, its audience, signature and recipients), not just that code ran.
- Don't lower the PHPStan level, the coverage minimum, or add a baseline. Use an inline `@phpstan-ignore <identifier>` with a reason only for genuine false positives.
- Schema changes need a migration in `src/migrations/` and a `schemaVersion` bump in `composer.json` `extra`.
- Keep style-only changes (`fix-cs`) in their own commit, separate from logic changes.
- Don't commit generated or local files: `tests/.env`, `tests/_output`, `tests/_support/_generated`, `vendor/`, `composer.lock`.

## Workflow

- Default branch is `master`. Work on a branch and open a PR; CI (`.github/workflows/ci.yml`) must be green.
- **PR titles and commit messages use [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/)**: `<type>(<scope>): <description>`, e.g. `fix(sso): ...` (see `CONTRIBUTING.md`). Always title PRs this way: the title is the squash-merge commit and decides the release. Pick the type by what users get: `fix:` for a bug or security fix, `feat:` for new behaviour, `!` for a breaking change to public API or config; otherwise a non-releasing type. If unsure whether a change should release, ask. Keep security-fix titles neutral (`fix: validate ...`): never name an unreleased vulnerability.
- SemVer from the PR title: `fix:`/`perf:` → patch, `feat:` → minor, `<type>!:` or a `BREAKING CHANGE:` line in the PR body → major. `docs`, `test`, `ci`, `chore`, `build`, `refactor`, `style` and `revert` don't release. A releasing PR adds its notes at the top of `CHANGELOG.md` under `## Unreleased` (with `### Fixed|Added|Changed|Removed`; `## Unreleased [CRITICAL]` for a critical release). Don't touch `composer.json` `version`/`extra.version` or write version headings. The "Release check" CI job enforces this.
- Merging to `master` releases once checks and tests pass: CI renames `Unreleased` to `## X.Y.Z YYYY-MM-DD`, sets the `composer.json` version, pushes a `chore(release): X.Y.Z` commit and the unprefixed tag (`5.0.10`) as the release GitHub App, and creates the GitHub release from that section (`.github/scripts/version.sh`). Never bump, tag or release manually.
- User docs live in `docs/` (VuePress, published to https://saml-idp.flipboxfactory.com from `master`). Update them with any user-facing change. Keep `README.md` short.

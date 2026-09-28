# Changelog

All notable changes to `laravel-magiclink` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

For versions before 2.24.2, see the [GitHub releases](https://github.com/cesargb/laravel-magiclink/releases).

## [Unreleased]

### Changed

- `MagicLinkController` no longer looks up the token itself. It only runs the `MagicLink`
  that `MagiclinkMiddleware` already validated and stored on the request, under
  `MagiclinkMiddleware::REQUEST_ATTRIBUTE`. If that attribute is missing, it returns the
  invalid-link response instead of running anything.

### Added

- Documented how to read the validated `MagicLink` from the request attribute when using a
  custom controller (`disable_default_route`). See the "Custom controller" section in the README.

### Deprecated

- `MagicLink::getMagicLinkByToken()` does not check expiration or visit limits. Use
  `getValidMagicLinkByToken()` instead, unless you intend to bypass those checks yourself.

### Upgrade notes

- If you replaced `MagiclinkMiddleware` with your own, or extended it without calling
  `parent::handle()`, you must now set the `MagiclinkMiddleware::REQUEST_ATTRIBUTE` request
  attribute yourself once the link is validated. Otherwise the default controller will treat
  every link as invalid.

## [2.28.1] - 2026-09-23

### Security

- **Access code brute force** — [GHSA-xc4p-3vgh-p5pf](https://github.com/cesargb/laravel-magiclink/security/advisories/GHSA-xc4p-3vgh-p5pf) (High).
  Access-code guesses on protected magic links were not limited by default, so an attacker
  holding a leaked link could brute-force its access code. Wrong guesses are now throttled
  **per magic link** (not per IP): after 5 failed attempts within a 300-second window, further
  attempts receive `429` with a `Retry-After` header. A valid access-code cookie is never
  throttled, and simply opening the link does not count as an attempt. New
  `MagicLink\Events\MagicLinkAccessCodeFailed` event, fired on every wrong access code.
- **One-time links consumed more than once** — [GHSA-4426-mcrr-gf94](https://github.com/cesargb/laravel-magiclink/security/advisories/GHSA-4426-mcrr-gf94) (Moderate).
  Links with `max_visits` could be used more times than configured under concurrent requests.
  The visit counter is now incremented atomically, and a request that loses the race gets the
  invalid-link response.

### Changed

- The default access-code form now submits via `POST`, so codes no longer end up in access
  logs, browser history or `Referer` headers. A new `POST` route is registered on the
  magic-link path; `GET ?access-code=` keeps working.
- The documented `access_code.view` config key is now honored. Before this release, only the
  undocumented `access-code.view` key worked; it is still supported.

### Upgrade notes

- No migration required. Existing published `config/magiclink.php` files keep working: the new
  keys fall back to safe defaults in code.
- The access-code limiter uses your application's **default cache store**. It must be shared
  across all processes serving the app (e.g. `redis`, `database`, `memcached`), not `array`.
- Set `access_code.max_attempts` to `0` or `'none'` to disable the limiter.
- `MagicLink::visited()` now returns `bool` (`false` when the link has no visits left). This
  only matters if you call or override it yourself.

## [2.28.0] - 2026-07-27

### Added

- `InlineFileAction`, to serve private files inline in the browser instead of forcing a
  download (#161).

### Removed

- Dropped Laravel 11 support from the dependency constraints (#160).

## [2.27.1] - 2026-04-20

### Changed

- Dependency maintenance (#155, #156).

## [2.27.0] - 2026-02-25

### Added

- Automatic pruning of expired magic links through Laravel's `MassPrunable` trait (#149).

### Changed

- Removed the `delete_massive` config key. `delete_expired_when_created` now controls cleanup
  on creation, and its **default changed from `true` to `false`** (#154).

### Upgrade notes

- If you relied on expired links being deleted automatically when a new one is created, set
  `MAGICLINK_DELETE_EXPIRED_WHEN_CREATED=true` explicitly, or use `php artisan model:prune`.

## [2.26.0] - 2026-02-17

### Added

- Configurable `allowed_classes` (and `MAGICLINK_ALLOWED_CLASSES` env var) to allowlist object
  properties in custom actions, with a `TypeError` when an action uses a class that isn't
  allowed (#151).
- Laravel 13 support (#152).

## [2.25.1] - 2026-02-12

### Security

- Completed the fix for [GHSA-r33w-fg8j-9c94](https://github.com/cesargb/laravel-magiclink/security/advisories/GHSA-r33w-fg8j-9c94)
  (insecure deserialization of actions) by removing support for the legacy serialized action
  format (#148).

### Upgrade notes

- If you have magic links created with version 2.24.1 or earlier, migrate them before
  upgrading:
  ```bash
  php artisan magiclink:migrate --dry-run
  php artisan magiclink:migrate
  ```

## [2.25.0] - 2026-02-04

### Added

- `magiclink:migrate --dry-run` option, to simulate the legacy-action migration and report how
  many links would be affected before running it for real (#146).

## [2.24.7] - 2026-01-29

### Fixed

- Legacy-actions migration command failing on PostgreSQL (#145).

## [2.24.6] - 2026-01-29

### Fixed

- Chunking issue in the legacy-actions migration command.

## [2.24.5] - 2026-01-29

### Added

- `magiclink:migrate` command, to migrate legacy serialized actions to the new format (#144).

## [2.24.4] - 2026-01-28

### Fixed

- Compatibility fix for the `allowed_classes` deserialization check.

## [2.24.3] - 2026-01-28

### Fixed

- Compatibility fix for the legacy action format.

## [2.24.2] - 2026-01-28

### Security

- **Insecure deserialization of MagicLink actions** — [GHSA-r33w-fg8j-9c94](https://github.com/cesargb/laravel-magiclink/security/advisories/GHSA-r33w-fg8j-9c94) (High).
  Actions were stored as raw PHP-serialized objects, without integrity validation or class
  allowlisting, which could lead to remote code execution if an attacker could manipulate the
  `magic_links` table. Actions are now stored as HMAC-signed JSON, with class allowlisting
  restricted to `ActionAbstract` subclasses and framework classes. Legacy serialized data is
  still read for backward compatibility (see [Migrate actions](README.md#migrate-actions)).

### Removed

- Dropped Laravel 10 support; added tests on PHP 8.5 (#137).

[Unreleased]: https://github.com/cesargb/laravel-magiclink/compare/v2.28.1...HEAD
[2.28.1]: https://github.com/cesargb/laravel-magiclink/compare/v2.28.0...v2.28.1
[2.28.0]: https://github.com/cesargb/laravel-magiclink/compare/v2.27.1...v2.28.0
[2.27.1]: https://github.com/cesargb/laravel-magiclink/compare/v2.27.0...v2.27.1
[2.27.0]: https://github.com/cesargb/laravel-magiclink/compare/v2.26.0...v2.27.0
[2.26.0]: https://github.com/cesargb/laravel-magiclink/compare/v2.25.1...v2.26.0
[2.25.1]: https://github.com/cesargb/laravel-magiclink/compare/v2.25.0...v2.25.1
[2.25.0]: https://github.com/cesargb/laravel-magiclink/compare/v2.24.7...v2.25.0
[2.24.7]: https://github.com/cesargb/laravel-magiclink/compare/v2.24.6...v2.24.7
[2.24.6]: https://github.com/cesargb/laravel-magiclink/compare/v2.24.5...v2.24.6
[2.24.5]: https://github.com/cesargb/laravel-magiclink/compare/v2.24.4...v2.24.5
[2.24.4]: https://github.com/cesargb/laravel-magiclink/compare/v2.24.3...v2.24.4
[2.24.3]: https://github.com/cesargb/laravel-magiclink/compare/v2.24.2...v2.24.3
[2.24.2]: https://github.com/cesargb/laravel-magiclink/compare/v2.24.1...v2.24.2

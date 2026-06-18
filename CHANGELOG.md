# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - Unreleased

### Added

- Login logs: failed login, login, and logout tracking with admin UI
- Bootstrap Logs menu (dropdown and sidebar), configurable via `.env`
- `SecureLoginFlow` middleware for login-screen security headers and pre-login block checks
- Publishable `idle-timeout.js` asset and Blade partial for app layouts
- Auth event listeners for automatic login block reset and login logging
- `login_logs` migration and related models, services, and controllers

### Changed

- Expanded `login_security` and `security_suite` configuration options
- README and Composer metadata updated for Packagist publication

## [1.0.0] - 2026-01-01

### Added

- Audit logging with admin viewer
- Password policy (complexity, expiry, history)
- Login block service (temporary lock and permanent block)
- Idle session timeout (server middleware + client ping routes)
- Password history admin UI
- Optional HTTP request logging with admin viewer
- Laravel auto-discovery service provider
- Database migrations and publishable config/views

[1.1.0]: https://github.com/imransaleem25/security-suite/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/imransaleem25/security-suite/releases/tag/v1.0.0

# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- `ImportUsersSeeder` for batch importing Pegawai and Mitra data.
- Support for importing Pegawai from `pegawai.json`.
- Support for importing Mitra from Excel files using `phpoffice/phpspreadsheet`.
- Identity columns to `users` table for SSO purposes (NIP, SOBAT ID, etc).
- Filament Shield for role/permission management.
- Laravel Passport for OAuth2 server functionality.

### Changed
- Refactored `README.md` to focus on SIPETRA SSO project details.
- Optimized seeder performance by caching password hashes.

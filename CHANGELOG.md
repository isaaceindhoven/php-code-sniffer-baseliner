# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Unreleased

### Added
- Add .project-inventory.json
- Add CHANGELOG.md

## v2.3.1 - 2022-11-16

### Fixed
- Fix totals parsing in ReportDeserializer (#11)

## v2.3.0 - 2022-11-04

### Added
- Add PHP 8.2 support (#10)

### Changed
- Simplify GitHub Actions (#10)

## v2.2.0 - 2022-05-23

### Added
- Add PHP 8.1 support

## v2.1.1 - 2021-07-20

### Fixed
- Don't add phpcs:ignore instructions inside doc comments to prevent messing up with multi-line annotations
- Support merging of phpcs:ignore instructions on first line

## v2.1.0 - 2021-04-30

### Changed
- Improve README
- Change license to MIT
- Make package open source
- Set up GitHub Actions

## v2.0.5 - 2021-03-10

### Fixed
- Fix bug regarding placement of phpcs:disable instruction directly after an phpcs:ignore instruction

## v2.0.4 - 2021-02-09

### Fixed
- Use 'phpcs:ignore' instead of '@phpcs:ignore' in comment blocks in order to not confuse annotation readers

## v2.0.3 - 2021-01-12

### Fixed
- Fix bug with wrong line endings

## v2.0.2 - 2021-01-12

### Fixed
- Fix bug with multiple violations in multi-line string

## v2.0.1 - 2021-01-12

### Fixed
- Fix star prefix in doc comment

## v2.0.0 - 2021-01-11

### Changed
- Complete rewrite of the tool: now adds ignore comments to source files instead of adding rule exclusions to the config file

## v1.1.0 - 2020-01-14

### Changed
- Make exclusions more specific

## v1.0.0 - 2020-01-13

### Added
- Initial release of the tool which creates a baseline by adding exclude-pattern entries to the phpcs config file

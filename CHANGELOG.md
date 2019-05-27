# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.2.2]

### Changed
- Avatar URL from webmentions is not stored by default (optional config)
- Empty/irrelevant fields no longer stored in comment meta

## [0.2.1]

### Added
- Failed webmentions are removed from queue

## [0.2.0]

### Added
- Alternative (grouped) presentation of comments in template
- Possibility to retrieve comments array for use in custom template

## [0.1.4]

### Added
- Manual webmention form below comment form

### Changed
- Token in cronjob URL moved to attribute
- Styling of comment listing improved (in content and style)

## [0.1.3]

### Added
- Helpers commentionsFeedback(), commentionsForm() and commentionsList()
- Spam filter based on time measuring

### Changed
- Helper commentions() is now a default shorthand for the three helpers commentionsFeedback(), commentionsForm() and commentionsList()

## [0.1.2]

### Changed
- Removed attributes from helper commentions()
- Renamed helper webmentionEndpoint() to commentionsEndpoints()
- Moved form CSS to asset file; can be included calling helper commentionsCss()

## [0.1.1]

### Added
- Simple form honeypot
- Configuration options for form fields

[Unreleased]: https://github.com/sebastiangreger/kirby3-commentions/compare/v0.2.2...HEAD
[0.2.2]: https://github.com/sebastiangreger/kirby3-commentions/compare/v0.2.1...v0.2.2
[0.2.1]: https://github.com/sebastiangreger/kirby3-commentions/compare/v0.2.0...v0.2.1
[0.2.0]: https://github.com/sebastiangreger/kirby3-commentions/compare/v0.1.4...v0.2.0
[0.1.4]: https://github.com/sebastiangreger/kirby3-commentions/compare/v0.1.3...v0.1.4
[0.1.3]: https://github.com/sebastiangreger/kirby3-commentions/compare/v0.1.2...v0.1.3
[0.1.2]: https://github.com/sebastiangreger/kirby3-commentions/compare/v0.1.1...v0.1.2
[0.1.1]: https://github.com/sebastiangreger/kirby3-commentions/compare/v0.1.0...v0.1.1
[0.1.0]: https://github.com/sebastiangreger/kirby3-commentions/releases/tag/v0.1.0

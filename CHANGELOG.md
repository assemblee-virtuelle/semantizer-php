# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed

- Rename method `IFactory:make` to `makeFromResource`.

### Added

- Add a resource parameter in the `SemanticObject` constructor.
- Add the `Semantizer:import` method.

### Removed

- Method `SemanticObjectAnonymous:makeFromResource` (moved into the `SemanticObject` constructor).

## [1.0.0-alpha4] - 2023-08-13

### Fixed

- Method `SemanticObject::setSemanticProperty` does add the blank node in the graph.

### Added

- Method `Semanticable:removeSemanticPropertyAll`.
- Method `SemanticObject:removeSemanticPropertyAll`.

## [1.0.0-alpha3] - 2023-08-09

### Fixed

- Method `SemanticObject::setSemanticProperty` allows to set blank node.

### Added

- Interface `IFactory`.
- Method `Semantizer::getPrefix`.
- Method `Semantizer::shorten`.
- Method `Semantizer::expand`.
- Class `Factory` in test.
- Class `SemanticObjectAnonymousSub` in test.

### Changed

- Rename tests folder to "test".
### Removed

- Dependency to phpunit (to avoid potential conflicts as suggested in the official documentation).

## [1.0.0-alpha2] - 2023-07-31

### Added

- Add a method to set a prefix.
- Add this changelog file.

## [1.0.0-alpha1] - 2023-07-28

- Initial release.

[unreleased]: https://github.com/assemblee-virtuelle/semantizer-php/compare/v1.0.0-alpha4...HEAD
[1.0.0-alpha4]: https://github.com/assemblee-virtuelle/semantizer-php/compare/v1.0.0-alpha3...v1.0.0-alpha4
[1.0.0-alpha3]: https://github.com/assemblee-virtuelle/semantizer-php/compare/v1.0.0-alpha2...v1.0.0-alpha3
[1.0.0-alpha2]: https://github.com/assemblee-virtuelle/semantizer-php/compare/v1.0.0-alpha1...v1.0.0-alpha2
[1.0.0-alpha1]: https://github.com/assemblee-virtuelle/semantizer-php/releases/tag/v1.0.0-alpha1

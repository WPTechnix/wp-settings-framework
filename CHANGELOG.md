# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres
to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-08-11

This is the initial public release of the WPTechnix Settings Framework. This version represents a complete architectural
overhaul from its internal, monolithic predecessor, refactoring it into a modern, decoupled, and highly extensible
framework ready for public use.

### Added

* **Complete Architectural Refactor:** The entire framework has been broken down into small, single-responsibility
  classes, following modern object-oriented design principles.
* **Composer Support:** The framework is now fully PSR-4 compliant and installable as a standard Composer package (
  `wptechnix/wp-settings-framework`).
* **Dependency Injection (DI) Friendly Design:**
    * Introduced a primary `Settings` class that acts as a cohesive manager for building a page and retrieving its data.
    * Added a `Interfaces\SettingsInterface` to allow for clean dependency injection and testability.
    * Removed all static accessors (`SettingsRegistry`) in favor of an object-oriented, injectable architecture.
* **Extensible Field System:**
    * Created a `FieldFactory` and an `AbstractField` base class, making it simple to add new, custom field types.
    * All 20+ field types are now individual classes, making the system easier to maintain and extend.
* **Dedicated Asset Management:** A new `AssetManager` class now handles all CSS and JavaScript enqueueing.
* **Intelligent Asset Loading:**
    * The `AssetManager` automatically detects which field types are being used and only enqueues the necessary
      libraries (e.g., Select2, Flatpickr, CodeMirror modes).
    * Includes a fallback mechanism to prevent conflicts: it will not load a library from its CDN if a theme or another
      plugin has already registered it.
* **Configurable HTML Class Prefixing:**
    * Introduced a new `htmlPrefix` option (defaults to `wptechnix-settings`) to prevent CSS and JS class name
      collisions in the WordPress admin.
    * This prefix is now consistently applied to all custom field elements and their containers.
* **Flexible `Settings` Builder:**
    * The `pageTitle` and `menuTitle` are now optional in the `Settings` constructor, defaulting to WordPress's
      standard "Settings" text for simpler setups.
    * Added fluent setter methods (`setPageTitle()`, `setMenuTitle()`) for more flexible configuration.
* **Enhanced Code Editor Field:** The `code` field now accepts a `language` parameter (`css`, `javascript`, `html`) to
  enable the correct syntax highlighting mode.
* **Full PHPDoc Coverage:** Every class, method, and property is now fully documented according to PHPDoc and PSR-12
  standards.
* **Comprehensive `README.md` and `CHANGELOG.md`:** Added official documentation for installation, usage, and project
  history.

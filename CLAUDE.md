# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a WordPress plugin called "Default Image Enlarge on click" that automatically sets the Image block's default Link setting to "Enlarge on click" (core lightbox) in the Block Editor. The plugin is a single-file WordPress plugin written in PHP.

## Architecture

The plugin uses WordPress's theme.json system to inject default settings for the core/image block:

- **Main file**: `default-image-expand.php` — Contains all plugin functionality
- **Editor script**: `editor.js` — Client-side block editor script
- **Namespace**: `Default_Image_Expand` — All functions are namespaced
- **Hook system**: Uses WordPress's `wp_theme_json_data_default` filter to modify theme configuration
- **Theme.json integration**: Modifies the lightbox settings for core/image blocks at the default layer only

## Key Components

### Plugin Functions

- `enable_image_enlarge_defaults_only()` — Merges lightbox defaults into theme.json data; returns unmodified when user has opted out
- `load_textdomain()` — Loads plugin translations
- `add_user_profile_field()` — Renders per-user opt-out checkbox on profile pages
- `save_user_profile_field()` — Persists user opt-out preference; includes nonce and capability checks
- `deactivation_cleanup()` — Deletes all user meta when plugin is deactivated

### WordPress Integration

Hooks registered at the bottom of `default-image-expand.php`:

- `init` → `load_textdomain()`
- `wp_theme_json_data_default` filter → `enable_image_enlarge_defaults_only()`
- `show_user_profile` / `edit_user_profile` → `add_user_profile_field()`
- `personal_options_update` / `edit_user_profile_update` → `save_user_profile_field()`
- `register_deactivation_hook` → `deactivation_cleanup()`

## Development Notes

- **WordPress version**: Requires WordPress 6.8+
- **PHP version**: Requires PHP 8.0+
- **Plugin header**: Standard WordPress plugin header with GPL-2.0-or-later license
- **Security**: Includes `ABSPATH` check to prevent direct access
- **Strict types**: `declare(strict_types=1)` at top of file; uses `use WP_Theme_JSON_Data` and `use WP_User` imports for unqualified type hints in a namespace context

## Plugin Behavior

- Sets `lightbox.enabled: true` for core/image blocks via the `wp_theme_json_data_default` layer
- Does not override existing user preferences or theme settings
- Per-user opt-out: stored as user meta key `disable_image_enlarge_on_click` (`'1'` = opted out, `''` = enabled); users manage this via their profile page
- Only affects new Image blocks; existing blocks with explicit settings remain unchanged

## Development Commands

```bash
composer install          # Install dependencies

composer lint             # Check for PHPCS violations (via emrikol/phpcs)
composer lint:fix         # Auto-fix PHPCS violations

composer test             # Run PHPUnit unit tests
composer test:coverage    # Run tests with code coverage (requires Xdebug)

composer git:tag -- VERSION    # Create and push an annotated git tag
composer git:release -- TAG    # Create a GitHub release (requires GITHUB_TOKEN env var)
```

## Code Quality

- Follows WordPress coding standards via `.phpcs.xml.dist`
- Custom sniffs via the `emrikol/phpcs` vendor package; `dealerdirect/phpcodesniffer-composer-installer` handles path registration automatically
- `testVersion` set to `8.0-`; `minimum_supported_wp_version` set to `6.8`
- If the plugin slug changes, update text domain in `.phpcs.xml.dist` and prefix in `WordPress.NamingConventions.PrefixAllGlobals`
- Inline comments must end in full stops, exclamation marks, or question marks
- Use Yoda condition checks
- Always add return type declarations and type hints for function arguments
- Always add `declare(strict_types=1);` to PHP files
- Never add `phpcs:ignore` or `phpcs:disable` without explicit user instruction

## Testing

Tests live in `tests/` and use an in-memory WordPress stub environment — no live WordPress installation or database required:

- `tests/bootstrap.php` — WordPress function and class stubs; `WP_Theme_JSON_Data` and `WP_User` stub classes record method calls for assertion
- `tests/unit/DefaultImageExpandTest.php` — Behavioural tests for all plugin functions (22 tests, 100% line coverage)
- Global stub state uses the `die_` prefix; each test's `setUp()` resets all globals to a clean baseline
- Never modify test expectations to make tests pass — fix the implementation instead

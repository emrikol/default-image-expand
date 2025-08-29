# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a WordPress plugin called "Default Image Expand on Click" that automatically sets the Image block's default Link setting to "Expand on click" (core lightbox) in the Block Editor. The plugin is a single-file WordPress plugin written in PHP.

## Architecture

The plugin uses WordPress's theme.json system to inject default settings for the core/image block:

- **Main file**: `default-image-expand.php` - Contains all plugin functionality
- **Namespace**: `Default_Image_Expand` - All functions are namespaced
- **Hook system**: Uses WordPress's `wp_theme_json_data_*` filters to modify theme configuration
- **Theme.json integration**: Modifies the lightbox settings for core/image blocks at the default and theme layers

## Key Components

### Core Functions

- `enable_image_expand_on_click()` - Merges lightbox defaults into theme.json data
- `register_theme_json_filters()` - Registers the theme.json filter hooks

### WordPress Integration

The plugin hooks into WordPress's theme setup process:
- Runs after theme setup via `after_setup_theme` action
- Modifies `wp_theme_json_data_default` and `wp_theme_json_data_theme` filters
- Option to force settings via `wp_theme_json_data_user` filter (commented out)

## Development Notes

- **WordPress version**: Requires WordPress 6.8+
- **PHP version**: Requires PHP 8.0+
- **Plugin header**: Standard WordPress plugin header with GPL-2.0-or-later license
- **Security**: Includes `ABSPATH` check to prevent direct access
- **Type hints**: Uses PHP 8.0+ type declarations (e.g., `WP_Theme_JSON_Data` return type)

## Plugin Behavior

- Sets `lightbox.enabled: true` for core/image blocks by default
- Does not override existing user preferences or theme settings
- Provides commented option to force lightbox even when users disable it
- Only affects new Image blocks; existing blocks with explicit settings remain unchanged

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Philosophy

### Core Ethos: KISS (Keep It Simple, Stupid)

- **Simplicity over cleverness**: If there's a simple solution and a complex one, choose simple
- **Avoid premature optimization**: Build for clarity first, optimize when needed
- **Question complexity**: If a solution feels complicated, step back and find the simpler path
- **One tool, one purpose**: Prefer focused tools over swiss army knives
- **Configuration over code**: Simple config changes beat code modifications
- **Boring technology**: Choose proven, stable solutions over cutting-edge complexity

---

## Language & Dependencies

### Dependency Management
- **Minimize dependencies** - favor "vanilla" implementations
- **ALWAYS ASK** before installing new dependencies
- When dependencies are necessary, choose well-established, minimal ones
- Document dependency choices and rationale in code comments

---

## Code Quality & Architecture

### Readability & Maintenance
- **Write for novice developers** - assume code will be read by others
- **Favor readable code over clever shortcuts**
- **Comment extensively** about architectural and design decisions
- **Explain obtuse/obfuscated code** when unavoidable due to external constraints

### SOLID Principles Focus
- **Single Responsibility Principle**: A class should have one, and only one, reason to change
- **Split by responsibility, not size**: If a class handles business logic + I/O + formatting, it's too large regardless of line count
- **Design for change**: Consider what would cause a component to need modification

### Modularity & Future-Proofing
- **Plugin architecture**: New platform parsers should integrate without core changes
- **Separation of concerns**: Keep parsing, indexing, and search as distinct modules
- **Configuration-driven**: Platform-specific settings in config files, not hardcoded

---

## Session & Context Management

### TODO.md Usage
- **Liberal updates** to track progress
- **Session continuity** - TODO.md is the source of truth if context is lost
- **Brain dump/scratchpad** for notes and intermediate thoughts
- **Mark completed tasks** with timestamps

### Context Optimization
- **Monitor context window usage**
- **Flag when sections are complete** and can be cleared
- **Optimize for performance** and reduce context poisoning
- **Clear completed work** to make room for new tasks

---

## Development Workflow

### Task Management
1. **Update TODO.md** before starting work
2. **Mark tasks in-progress** while working
3. **Complete tasks** with completion notes
4. **Flag context optimization opportunities**

### Code Review Principles
- **Self-document architectural decisions**
- **Comment non-obvious implementations**
- **Flag potential refactoring opportunities**
- **Consider modularity for future platforms**

### Decision Making & Recommendations
- **Be opinionated**: When presenting options, research and recommend the best choice
- **Provide evidence**: Back recommendations with research, benchmarks, or best practices
- **Default to expertise**: User relies on Claude's knowledge for technical decisions
- **Present reasoning**: Explain why a particular choice is recommended
- **Research before asking**: Use available tools to gather evidence for recommendations
- **Verify facts with tools**: Use bash commands, web searches, and file reads to verify information
- **Check dates and versions**: Always use `date` command and version checks rather than assumptions

---

*This document is living - update as development principles evolve.*

---

## Common Development Commands

### Setup and Dependencies
```bash
# Install dependencies
composer install

# All dependencies are included - no additional setup needed
```

### Code Quality

**IMPORTANT: Code Quality Process**

* Inline comments must end in full-stops, exclamation marks, or question marks
* Use Yoda Condition checks, you must
* Always add return type declrations for functions
* Always add type hinting for function arguments
* Always add `declare(strict_types=1);` strict type declaration to the top of PHP files

After making any code changes, always run these commands in order:

**For PHP files:**
1. **Auto-fix violations**: `composer fix` or `composer phpcbf path/to/file.php` (fixes what it can automatically)
2. **Check remaining issues**: `composer lint` or `composer phpcs path/to/file.php` (reports remaining violations)

**Note**: The `composer phpcs` and `composer phpcbf` commands automatically use the project's `vendor/bin/phpcs` if available in the git repository root.
3. **Fix manually**: Address any remaining PHPCS violations
4. **Never ignore**: Do not add `phpcs:ignore` or `phpcs:disable` comments unless the user explicitly requests it

**Example workflow:**
```bash
# Make code changes
# Then run:

# For PHP (direct commands):
composer fix                 # Auto-fix formatting, spacing, etc. in current directory
composer lint                # Check for remaining violations in current directory
# Or for specific files:
composer phpcbf path/to/file.php
composer phpcs path/to/file.php

# Fix any reported issues manually
# Ask user for guidance if unsure how to fix something
```

**Important Notes:**

**For PHP:**
- `phpcbf` (PHP Code Beautifier and Fixer) automatically fixes many formatting issues
- `phpcs` (PHP_CodeSniffer) reports remaining violations that need manual fixing
- Only add `phpcs:ignore` comments when the user specifically instructs you to do so
- Always ask the user for guidance if you're unsure how to fix a PHPCS violation

### Basic PHP Syntax Checking
```bash
# Check PHP syntax for all files
find . -name "*.php" -exec php -l {} \;

# Check and lint specific core files
php -l path/to/file.php
```

### Testing and Quality Assurance

**CRITICAL: Test Integrity Philosophy**

**DO THE WORK - DON'T CHEAT THE TESTS**

Tests are a critical part of our development cycle and must be treated with absolute integrity:

- **Fix the code, not the test** - When tests fail, the priority is to fix the underlying implementation
- **Tests reflect requirements** - Failing tests indicate the code doesn't meet specifications
- **No shortcuts or workarounds** - Never modify tests to pass without fixing the actual issue
- **Understand failures** - Investigate why tests fail before making any changes
- **Maintain test quality** - Tests should be as well-written and maintained as production code

**Test-Driven Development Approach:**
1. **Read the failing test** - Understand what behavior is expected
2. **Analyze the gap** - Identify what's missing or broken in the implementation
3. **Fix the implementation** - Make the minimal changes needed to satisfy the test
4. **Verify the fix** - Ensure tests pass for the right reasons
5. **Refactor if needed** - Improve code quality while keeping tests green

**When Tests Fail:**
- **Never ignore failing tests** - All tests must pass before considering work complete
- **Don't disable or skip tests** - Address the root cause instead
- **Don't modify test expectations** - unless requirements have genuinely changed
- **Document any test changes** - Explain why test modifications were necessary

```bash
# Run PHP unit tests using composer (recommended):
composer test

# or directly:
./run-tests.sh
```

### WordPress Options Guidelines

**IMPORTANT: WordPress Options Autoload Policy**

When using `update_option()` or `add_option()`, always explicitly set the `autoload` parameter to `false` unless the user specifically requests autoloading:

```php
// Correct - prevents unnecessary autoloading
update_option( 'my_plugin_setting', $value, false );
add_option( 'my_plugin_data', $data, '', false );

// Incorrect - causes autoloading on every page load
update_option( 'my_plugin_setting', $value );
add_option( 'my_plugin_data', $data );
```

**Rationale:**
- Plugin settings and data are rarely needed on every WordPress page load
- Autoloaded options increase memory usage and slow down site performance
- Most plugin data should only be loaded when specifically requested
- Only set autoload to `true` when the user explicitly requests it for frequently-accessed data

**Examples of options that should NOT autoload:**
- Configuration settings (API keys, remote site configs)
- Historical data (metrics, logs, cached results)
- Admin-only settings (debug flags, collection preferences)
- Large datasets (export data, bulk operations)

This applies to all options created or updated by the plugin.

### Code Quality Standards

**PHP Standards**
- Follows WordPress coding standards via `.phpcs.xml.dist`
- Minimum PHP 8.0 compatibility required
- Custom sniffs in `phpcs/` directory for additional validation
- Uupdate text domain in .phpcs.xml.dist if changed
- Prefixes in .phpcs.xml.dist for global functions/classes

---

## TODO.md Formatting Guidelines

### Proper TODO Format Structure

TODO.md should use checkable format with clear priority levels and organization:

**Priority Levels:**
- `🔴 HIGH PRIORITY (MUST FIX)` - Critical issues that must be addressed
- `🟡 MEDIUM PRIORITY (SHOULD FIX)` - Important improvements 
- `🟢 LOW PRIORITY (NICE TO HAVE)` - Optional enhancements

**Checkbox Format:**
```markdown
- [ ] **Task Name** - Description
  - Sub-task details
  - Specific requirements
- [x] **Completed Task** - What was accomplished
```

**Required Sections:**
1. **Priority-based task sections** with color coding
2. **VALIDATION TASKS** - Steps to run after each change
3. **DOCUMENTATION UPDATES** - What docs need updating
4. **ANALYSIS COMPLETED** - Checkoff completed research/analysis

**Example Structure:**
```markdown
# TODO: [Project Area]

## HIGH PRIORITY (MUST FIX) 🔴
- [ ] **Critical Task** - Why it's important
  - Specific implementation details
  - Methods/files to modify

## VALIDATION TASKS ✅
- [ ] **Run PHP syntax check**: `find . -name "*.php" -exec php -l {} \;`
- [ ] **Run PHPCS**: `phpcbf --extensions=php .`

## ANALYSIS COMPLETED ✅
- [x] **Research completed** - What was found
```

### TODO.md Best Practices

- **Use checkboxes** - `- [ ]` for pending, `- [x]` for completed
- **Be specific** - Include file paths, method names, line numbers
- **Add context** - Explain why tasks are needed
- **Update frequently** - Mark completed items immediately
- **Include validation** - Always have testing/quality check tasks
- **Reference locations** - Use `file:line` format for easy navigation
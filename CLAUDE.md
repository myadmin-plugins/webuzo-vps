# MyAdmin Webuzo VPS Plugin

Webuzo/Softaculous VPS management plugin for the MyAdmin control panel. Provides script installation, domain management, backup operations, and system application control via the Webuzo API.

## Commands

```bash
composer install                    # install PHP dependencies
phpunit                             # run all tests
phpunit --filter PluginTest         # run single test class
phpunit --coverage-text             # run with coverage report
```

## Architecture

**Namespace**: `Detain\MyAdminWebuzo\` → `src/` · **Tests**: `Detain\MyAdminWebuzo\Tests\` → `tests/` · **Autoload**: PSR-4 via `composer.json`

**Plugin entry**: `src/Plugin.php` — registers hooks via `getHooks()` → `function.requirements` event · `getRequirements()` maps page names to `src/` files via `$loader->add_page_requirement()`

**SDK layer** (vendored, no namespace):
- `src/sdk.php` — `Softaculous_SDK` base class: `curl()`, `curl_call()`, `install()`, `list_scripts()`, `backup()`, `restore()`
- `src/webuzo_sdk.php` — `Webuzo_API` extends `Softaculous_API` (extends `Softaculous_SDK`): `webuzo_configure()`, `install_app()`, `remove_app()`, `list_apps()`, `list_domains()`, `add_domain()`, `delete_domain()`, `list_installed_apps()`

**Page functions** (procedural, one per file in `src/`):
- `src/webuzo_configure.php` — initial VPS setup: LAMP install, license, password, email notification
- `src/webuzo_scripts.php` — main script browser: list, search, filter, install scripts
- `src/webuzo_view_script.php` — script detail view with install form
- `src/webuzo_list_installed_scripts.php` — installed software list with backup/remove/edit actions
- `src/webuzo_edit_installation.php` — edit installation details (dir, URL, DB)
- `src/webuzo_import_script.php` — import existing script installation
- `src/webuzo_remove_script.php` — remove installed script
- `src/webuzo_add_domain.php` · `src/webuzo_remove_domain.php` · `src/webuzo_list_domains.php` — domain CRUD
- `src/webuzo_list_backups.php` — backup listing with download/restore/delete
- `src/webuzo_list_sysapps.php` · `src/webuzo_view_sysapps.php` · `src/webuzo_list_installed_sysapps.php` · `src/webuzo_install_sysapp.php` — system application management
- `src/webuzo_update_logo.php` — update Webuzo site title
- `src/webuzo_randomPassword.php` — generate random alphanumeric password

**Shared helpers**: `src/webuzo.functions.inc.php` — `webuzo_api_call()` (raw curl to Webuzo API), `webuzo_get_all_scripts()`, `webuzo_add_backup()`, `webuzo_download_backup()`, `webuzo_remove_backup()`, `webuzo_restore_backup()`, `webuzo_format_units_size()`

## Test Structure

**Config**: `phpunit.xml.dist` — bootstrap `vendor/autoload.php`, testsuite `tests/`, coverage on `src/`

**Test files** in `tests/`:
- `tests/PluginTest.php` — verifies `Plugin` static properties, hook registration, `getRequirements()` page mappings
- `tests/WebuzoApiTest.php` — `Webuzo_API` class: constructor, method signatures, property defaults, error handling
- `tests/SoftaculousSdkTest.php` — `Softaculous_SDK` class: properties, method signatures, inheritance
- `tests/WebuzoFunctionsTest.php` — `webuzo_format_units_size()` and `webuzo_randomPassword()` unit tests
- `tests/FileExistenceTest.php` — asserts all expected `src/` files exist

Run specific test suites:

```bash
phpunit --filter WebuzoApiTest      # run Webuzo API tests
phpunit --filter SoftaculousSdkTest # run SDK tests
phpunit --filter WebuzoFunctionsTest # run function tests
```

## Patterns & Conventions

- **Page functions**: one function per file, named matching the filename. Uses `$GLOBALS['tf']->variables->request` for request params, `add_output()` for HTML, `TFTable` for form building, `verify_csrf_referrer()` for CSRF
- **DB access**: `get_service($id, 'vps')` for service lookup, `get_module_db('vps')` for DB handle, `$db->query()` / `$db->next_record(MYSQL_ASSOC)` / `$db->Record`
- **API calls**: `webuzo_api_call($host, $user, $pass, $act, $last_params, $post)` for raw HTTP, or instantiate `Webuzo_API` directly
- **Logging**: `myadmin_log('vps', $level, $message, __LINE__, __FILE__)`
- **Deserialization**: `myadmin_unstringify($response)` to parse API responses
- **Credentials**: stored in `history_log` table under `history_old_value = 'Webuzo Details'`
- **Event hooks**: Symfony `GenericEvent` with `function.requirements` for lazy-loading page functions
- **Tests**: PHPUnit 9, `declare(strict_types=1)`, reflection-based signature verification, no external API calls
- **Commit style**: lowercase, descriptive (`updates`, `fix for docker`, `php7.4 updates`)

## Dependencies

- PHP >= 7.4, `ext-soap`, `ext-curl`
- `symfony/event-dispatcher`
- `detain/myadmin-plugin-installer` dev-master
- Dev: `phpunit/phpunit` ^9.6

## CI

- GitHub Actions: `.github/workflows/tests.yml`
- Legacy: `.travis.yml` (PHP 5.4–7.1), `.scrutinizer.yml`, `.codeclimate.yml`, `.bettercodehub.yml`

Validate project configuration:

```bash
composer validate                   # validate composer.json
composer dump-autoload              # regenerate autoloader
```

<!-- caliber:managed:pre-commit -->
## Before Committing

**IMPORTANT:** Before every git commit, you MUST ensure Caliber syncs agent configs with the latest code changes.

First, check if the pre-commit hook is already installed:
```bash
grep -q "caliber" .git/hooks/pre-commit 2>/dev/null && echo "hook-active" || echo "no-hook"
```

- If **hook-active**: the hook handles sync automatically — just commit normally. Tell the user: "Caliber will sync your agent configs automatically via the pre-commit hook."
- If **no-hook**: run Caliber manually before committing:
  1. Tell the user: "Caliber: Syncing agent configs with your latest changes..."
  2. Run: `caliber refresh && git add CLAUDE.md .claude/ .cursor/ .cursorrules .github/copilot-instructions.md .github/instructions/ AGENTS.md CALIBER_LEARNINGS.md .agents/ .opencode/ 2>/dev/null`
  3. After it completes, briefly tell the user what Caliber updated. Then proceed with the commit.

If `caliber` is not found, tell the user: "This project uses Caliber for agent config sync. Run /setup-caliber to get set up."
<!-- /caliber:managed:pre-commit -->

<!-- caliber:managed:learnings -->
## Session Learnings

Read `CALIBER_LEARNINGS.md` for patterns and anti-patterns learned from previous sessions.
These are auto-extracted from real tool usage — treat them as project-specific rules.
<!-- /caliber:managed:learnings -->

<!-- caliber:managed:sync -->
## Context Sync

This project uses [Caliber](https://github.com/caliber-ai-org/ai-setup) to keep AI agent configs in sync across Claude Code, Cursor, Copilot, and Codex.
Configs update automatically before each commit via `caliber refresh`.
If the pre-commit hook is not set up, run `/setup-caliber` to configure everything automatically.
<!-- /caliber:managed:sync -->

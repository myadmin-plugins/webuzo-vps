---
name: phpunit-tests
description: Writes PHPUnit 9 tests following existing patterns in `tests/`. Uses `declare(strict_types=1)`, `ReflectionClass` for signature verification, data providers, and no external API calls. Use when user says 'add test', 'write tests', 'test coverage', or creates new `src/` files that need test coverage. Do NOT use for integration tests that require live API connections.
---
# PHPUnit Tests

## Critical

- **Never make live API calls.** All tests must run offline. Pre-populate object properties (e.g., `$api->apps = [...]`) to avoid triggering `curl` calls in SDK classes.
- **Always use `declare(strict_types=1);`** as the first statement after `<?php` in every test file.
- **Namespace:** `Detain\MyAdminWebuzo\Tests\` — every test file must use this namespace.
- **Extends:** `PHPUnit\Framework\TestCase` — never extend a custom base class.
- **File naming:** Test class name must match the filename exactly (e.g., `PluginTest` in `tests/PluginTest.php`).
- **Run tests before committing:** `phpunit` must pass with zero failures.

## Instructions

### Step 1: Determine what to test

Identify the target in `src/`. The project has three categories:

| Category | Example target | Test pattern |
|---|---|---|
| PSR-4 class | `src/Plugin.php` | `ReflectionClass` for structure, stub objects for behavior |
| Vendored SDK class (no namespace) | `src/sdk.php`, `src/webuzo_sdk.php` | `require_once` in `setUp()`, `ReflectionClass`, pre-populate properties |
| Procedural function file | `src/webuzo.functions.inc.php`, `src/webuzo_randomPassword.php` | `require_once` in `setUpBeforeClass()`, call functions directly |

Verify the target file exists in `src/` before proceeding.

### Step 2: Create the test file

Create a new test file in `tests/` following the naming convention (e.g., `tests/PluginTest.php`, `tests/WebuzoApiTest.php`). Use this exact boilerplate:

```php
<?php

declare(strict_types=1);

namespace Detain\MyAdminWebuzo\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Tests for {description of target}.
 *
 * {One line about what aspects are validated.}
 */
class WebuzoApiTest extends TestCase
{
}
```

Add imports as needed:
- `use ReflectionClass;` — for class structure/signature tests
- `use Detain\MyAdminWebuzo\Plugin;` — for PSR-4 classes
- `use Webuzo_API;` / `use Softaculous_SDK;` — for vendored SDK classes (global namespace)
- `use Symfony\Component\EventDispatcher\GenericEvent;` — only when testing Plugin hook handlers

Verify: the file is in `tests/` and ends with `Test.php`.

### Step 3: Set up class loading

For **PSR-4 classes** (`src/Plugin.php`): No special loading needed — autoloader handles it.

For **vendored SDK classes** without namespaces (`src/sdk.php`, `src/webuzo_sdk.php`): Use `require_once` in `setUp()`:

```php
protected function setUp(): void
{
    require_once dirname(__DIR__) . '/src/webuzo_sdk.php';
    $this->reflection = new ReflectionClass(Webuzo_API::class);
}
```

For **procedural function files**: Use `setUpBeforeClass()` (loads once for all tests):

```php
public static function setUpBeforeClass(): void
{
    require_once dirname(__DIR__) . '/src/webuzo.functions.inc.php';
}
```

Verify: `phpunit --filter WebuzoApiTest` loads without errors.

### Step 4: Write structural tests

For classes, always start with these structural tests:

1. **Class existence:** `$this->assertTrue(class_exists(TargetClass::class));`
2. **Instantiation:** `$this->assertInstanceOf(TargetClass::class, new TargetClass(...));`
3. **Inheritance:** `$this->assertTrue($this->reflection->isSubclassOf(ParentClass::class));`
4. **Property existence and visibility:**

```php
public function testPublicPropertiesExist(): void
{
    $expectedProps = ['login', 'debug', 'error'];
    foreach ($expectedProps as $prop) {
        $this->assertTrue(
            $this->reflection->hasProperty($prop),
            "Property \${$prop} should exist"
        );
        $this->assertTrue(
            $this->reflection->getProperty($prop)->isPublic(),
            "Property \${$prop} should be public"
        );
    }
}
```

5. **Method existence:**

```php
public function testExpectedMethodsExist(): void
{
    $methods = ['method_a', 'method_b'];
    foreach ($methods as $method) {
        $this->assertTrue(
            $this->reflection->hasMethod($method),
            "Method {$method}() should exist on TargetClass"
        );
    }
}
```

6. **Static method checks** (for Plugin-style classes):

```php
public function testGetHooksIsStatic(): void
{
    $method = $this->reflection->getMethod('getHooks');
    $this->assertTrue($method->isStatic());
    $this->assertTrue($method->isPublic());
}
```

For functions, start with existence: `$this->assertTrue(function_exists('function_name'));`

Verify: all structural tests pass.

### Step 5: Write method signature tests

Use `ReflectionClass` to verify parameter names, order, and defaults without calling the method:

```php
public function testAddDomainSignature(): void
{
    $method = $this->reflection->getMethod('add_domain');
    $params = $method->getParameters();
    $this->assertSame('domain', $params[0]->getName());
    $this->assertSame('domainpath', $params[1]->getName());
    $this->assertTrue($params[1]->isDefaultValueAvailable());
    $this->assertSame('', $params[1]->getDefaultValue());
}
```

For methods accepting `GenericEvent` (Plugin handlers):

```php
public function testGetRequirementsAcceptsGenericEvent(): void
{
    $method = $this->reflection->getMethod('getRequirements');
    $params = $method->getParameters();
    $this->assertCount(1, $params);
    $this->assertSame('event', $params[0]->getName());
}
```

Verify: signature tests cover all public methods on the target class.

### Step 6: Write behavioral tests (no network)

Test behavior by pre-populating object state to bypass curl calls:

```php
public function testInstallAppReturnsFalseWhenAppNotFound(): void
{
    $api = new Webuzo_API('u', 'p', 'h');
    // Pre-load apps so it doesn't try to curl
    $api->apps = [100 => ['name' => 'SomeApp']];

    $result = $api->install_app(999);
    $this->assertFalse($result);
    $this->assertContains('App Not Found', $api->error);
}
```

For Plugin hook handlers, use anonymous class stubs:

```php
public function testGetRequirementsRegistersPages(): void
{
    $registered = [];
    $loader = new class($registered) {
        private array $ref;
        public function __construct(array &$ref) { $this->ref = &$ref; }
        public function add_page_requirement(string $name, string $path): void {
            $this->ref[] = [$name, $path];
        }
    };

    $event = new GenericEvent($loader);
    Plugin::getRequirements($event);

    $this->assertNotEmpty($registered, 'Should register at least one page');
}
```

For pure functions, test directly with known inputs:

```php
public function testFormatKilobytes(): void
{
    $result = webuzo_format_units_size(1024);
    $this->assertSame('1.00 KB', $result);
}
```

Verify: no test triggers network calls.

### Step 7: Add data providers for repetitive cases

Use `@dataProvider` when testing the same assertion across many inputs:

```php
/**
 * @return array<string, array{0: string}>
 */
public function sourceFileProvider(): array
{
    return [
        'Plugin.php' => ['Plugin.php'],
        'sdk.php' => ['sdk.php'],
    ];
}

/**
 * @dataProvider sourceFileProvider
 */
public function testSourceFileExists(string $filename): void
{
    $this->assertFileExists(
        $this->srcDir . '/' . $filename,
        "Source file {$filename} should exist in src/"
    );
}
```

Data provider method must be `public`, return `array<string, array>`, and use descriptive string keys.

Verify: `phpunit --filter WebuzoApiTest` passes all tests.

### Step 8: Run full suite

Run `phpunit` from the package root. All tests must pass. Check coverage with `phpunit --coverage-text` if the target has testable logic.

Verify: zero failures, zero errors, zero warnings.

## Examples

### Example: User says "add tests for the Webuzo SDK"

**Actions taken:**
1. Read `src/webuzo_sdk.php` to identify the `Webuzo_API` class, its parent, properties, and methods.
2. Create `tests/WebuzoApiTest.php` with `declare(strict_types=1)`, namespace `Detain\MyAdminWebuzo\Tests`.
3. Add `require_once dirname(__DIR__) . '/src/webuzo_sdk.php';` in `setUp()` since `Webuzo_API` has no PSR-4 autoloading.
4. Store `$this->reflection = new ReflectionClass(Webuzo_API::class);` in `setUp()`.
5. Write structural tests: class exists, extends `Softaculous_API`, constants defined, properties exist and are public.
6. Write signature tests for each public method: parameter names, count, default values.
7. Write behavioral tests: `install_app(999)` returns `false` with pre-populated `$api->apps` array to avoid curl.
8. Run `phpunit --filter WebuzoApiTest` — all pass.

**Result:** `tests/WebuzoApiTest.php` with ~25 tests covering structure, signatures, and offline behavior.

### Example: User says "write tests for webuzo_format_units_size"

**Actions taken:**
1. Read `src/webuzo.functions.inc.php` to find the function signature and logic.
2. Create `tests/WebuzoFunctionsTest.php` (or add to existing).
3. Use `setUpBeforeClass()` with `require_once` for the functions file.
4. Write function existence test.
5. Write value tests: 0 bytes, 1 byte, 1024 (KB), 1048576 (MB), 1073741824 (GB), edge cases.
6. Run `phpunit --filter WebuzoFunctionsTest` — all pass.

**Result:** `tests/WebuzoFunctionsTest.php` with tests for each unit threshold and edge cases.

## Common Issues

**Error: `Class "Webuzo_API" not found`**
1. The vendored SDK classes have no namespace and aren't autoloaded.
2. Add `require_once dirname(__DIR__) . '/src/webuzo_sdk.php';` in your test's `setUp()` method.
3. For `Softaculous_SDK`, require `src/sdk.php` instead.

**Error: `Class "Detain\MyAdminWebuzo\Tests\PluginTest" not found`**
1. Check the namespace declaration matches `Detain\MyAdminWebuzo\Tests`.
2. Check the class name matches the filename exactly (e.g., `tests/PluginTest.php` contains `class PluginTest`).
3. Verify `composer.json` has the `autoload-dev` PSR-4 entry mapping `Detain\MyAdminWebuzo\Tests\` to `tests/`.

**Error: `Failed asserting that false is true` in method existence test**
1. The method name may be misspelled. Check the actual source file for exact method names.
2. SDK methods use `snake_case` (e.g., `list_apps`, `install_app`, `add_domain`).
3. Plugin methods use `camelCase` (e.g., `getHooks`, `getRequirements`, `getSettings`).

**Tests trigger actual HTTP/curl calls and hang or fail**
1. Pre-populate the object's data properties before calling methods that check them: `$api->apps = [...]`, `$sdk->scripts = [...]`, `$sdk->iscripts = [...]`.
2. SDK methods like `list_apps()`, `list_scripts()` return `true` immediately if their data arrays are non-empty.
3. Never instantiate `Webuzo_API` and call methods that hit the network — always stub the data.

**Error: `Risky test` or `This test did not perform any assertions`**
1. `phpunit.xml.dist` has `failOnRisky="true"`. Every test method must contain at least one assertion.
2. If testing that a method doesn't throw, add `$this->assertTrue(true);` after the call.

**Data provider returns wrong format**
1. Must return `array<string, array>` — outer keys are test case labels, inner values are arrays of arguments.
2. Method must be `public` and annotated with no arguments.
3. The `@dataProvider` annotation references the method name without `()` or `$this->`.
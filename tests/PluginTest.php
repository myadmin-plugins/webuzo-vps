<?php

declare(strict_types=1);

namespace Detain\MyAdminWebuzo\Tests;

use Detain\MyAdminWebuzo\Plugin;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Tests for the Plugin class.
 *
 * Validates class structure, static properties, hook registration,
 * and event handler signatures.
 */
class PluginTest extends TestCase
{
    /**
     * @var ReflectionClass<Plugin>
     */
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        $this->reflection = new ReflectionClass(Plugin::class);
    }

    /**
     * Test that the Plugin class exists and is instantiable.
     */
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(Plugin::class));
    }

    /**
     * Test that the Plugin class can be instantiated.
     */
    public function testPluginCanBeInstantiated(): void
    {
        $plugin = new Plugin();
        $this->assertInstanceOf(Plugin::class, $plugin);
    }

    /**
     * Test that the $name static property is set correctly.
     */
    public function testNameProperty(): void
    {
        $this->assertSame('Webuzo Plugin', Plugin::$name);
    }

    /**
     * Test that the $description static property is set correctly.
     */
    public function testDescriptionProperty(): void
    {
        $this->assertSame('Allows handling of Webuzo HTML5 VNC Connections', Plugin::$description);
    }

    /**
     * Test that the $help static property is an empty string.
     */
    public function testHelpProperty(): void
    {
        $this->assertSame('', Plugin::$help);
    }

    /**
     * Test that the $type static property is 'plugin'.
     */
    public function testTypeProperty(): void
    {
        $this->assertSame('plugin', Plugin::$type);
    }

    /**
     * Test that all expected static properties exist.
     */
    public function testStaticPropertiesExist(): void
    {
        $expected = ['name', 'description', 'help', 'type'];
        foreach ($expected as $prop) {
            $this->assertTrue(
                $this->reflection->hasProperty($prop),
                "Property \${$prop} should exist on Plugin"
            );
            $this->assertTrue(
                $this->reflection->getProperty($prop)->isStatic(),
                "Property \${$prop} should be static"
            );
            $this->assertTrue(
                $this->reflection->getProperty($prop)->isPublic(),
                "Property \${$prop} should be public"
            );
        }
    }

    /**
     * Test that getHooks returns an array.
     */
    public function testGetHooksReturnsArray(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertIsArray($hooks);
    }

    /**
     * Test that getHooks registers the function.requirements hook.
     */
    public function testGetHooksContainsFunctionRequirements(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertArrayHasKey('function.requirements', $hooks);
    }

    /**
     * Test that the function.requirements hook points to getRequirements.
     */
    public function testFunctionRequirementsHookCallback(): void
    {
        $hooks = Plugin::getHooks();
        $callback = $hooks['function.requirements'];
        $this->assertIsArray($callback);
        $this->assertCount(2, $callback);
        $this->assertSame(Plugin::class, $callback[0]);
        $this->assertSame('getRequirements', $callback[1]);
    }

    /**
     * Test that getHooks method is static.
     */
    public function testGetHooksIsStatic(): void
    {
        $method = $this->reflection->getMethod('getHooks');
        $this->assertTrue($method->isStatic());
    }

    /**
     * Test that getMenu method exists and is static.
     */
    public function testGetMenuIsStatic(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getMenu'));
        $method = $this->reflection->getMethod('getMenu');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    /**
     * Test that getRequirements method exists and is static.
     */
    public function testGetRequirementsIsStatic(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getRequirements'));
        $method = $this->reflection->getMethod('getRequirements');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    /**
     * Test that getSettings method exists and is static.
     */
    public function testGetSettingsIsStatic(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getSettings'));
        $method = $this->reflection->getMethod('getSettings');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    /**
     * Test that getMenu accepts a GenericEvent parameter.
     */
    public function testGetMenuAcceptsGenericEvent(): void
    {
        $method = $this->reflection->getMethod('getMenu');
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());
    }

    /**
     * Test that getRequirements accepts a GenericEvent parameter.
     */
    public function testGetRequirementsAcceptsGenericEvent(): void
    {
        $method = $this->reflection->getMethod('getRequirements');
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());
    }

    /**
     * Test that getSettings accepts a GenericEvent parameter.
     */
    public function testGetSettingsAcceptsGenericEvent(): void
    {
        $method = $this->reflection->getMethod('getSettings');
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());
    }

    /**
     * Test that the constructor takes no parameters.
     */
    public function testConstructorHasNoParameters(): void
    {
        $constructor = $this->reflection->getConstructor();
        $this->assertNotNull($constructor);
        $this->assertCount(0, $constructor->getParameters());
    }

    /**
     * Test that getRequirements calls add_page_requirement on the loader subject.
     *
     * Uses an anonymous class to stub the loader dependency.
     */
    public function testGetRequirementsRegistersPages(): void
    {
        $registered = [];
        $loader = new class($registered) {
            /** @var array<int, array{0: string, 1: string}> */
            private array $ref;
            public function __construct(array &$ref)
            {
                $this->ref = &$ref;
            }
            public function add_page_requirement(string $name, string $path): void
            {
                $this->ref[] = [$name, $path];
            }
        };

        $event = new GenericEvent($loader);
        Plugin::getRequirements($event);

        $this->assertNotEmpty($registered, 'getRequirements should register at least one page');

        $names = array_column($registered, 0);
        $expectedPages = [
            'webuzo_configure',
            'webuzo_scripts',
            'webuzo_edit_installation',
            'webuzo_install_sysapp',
            'webuzo_add_domain',
            'webuzo_list_sysapps',
            'webuzo_randomPassword',
            'webuzo_remove_script',
            'webuzo_view_script',
            'webuzo_list_installed_scripts',
            'webuzo_import_script',
            'webuzo_list_backups',
            'webuzo_list_domains',
            'webuzo_remove_domain',
            'webuzo_view_sysapps',
            'webuzo_list_installed_sysapps',
            'webuzo_update_logo',
            'webuzo_get_all_scripts',
            'webuzo_add_backup',
            'webuzo_download_backup',
            'webuzo_remove_backup',
            'webuzo_restore_backup',
            'webuzo_api_call',
            'webuzo_format_units_size',
        ];
        foreach ($expectedPages as $page) {
            $this->assertContains($page, $names, "Expected page requirement '{$page}' to be registered");
        }
    }

    /**
     * Test that all registered paths reference vendor/detain/myadmin-webuzo-vps/src/.
     */
    public function testGetRequirementsPathsPointToSrc(): void
    {
        $registered = [];
        $loader = new class($registered) {
            private array $ref;
            public function __construct(array &$ref)
            {
                $this->ref = &$ref;
            }
            public function add_page_requirement(string $name, string $path): void
            {
                $this->ref[] = [$name, $path];
            }
        };

        $event = new GenericEvent($loader);
        Plugin::getRequirements($event);

        foreach ($registered as [$name, $path]) {
            $this->assertStringContainsString(
                'vendor/detain/myadmin-webuzo-vps/src/',
                $path,
                "Path for '{$name}' should reference the src directory"
            );
        }
    }

    /**
     * Test that the Plugin class resides in the expected namespace.
     */
    public function testClassNamespace(): void
    {
        $this->assertSame('Detain\MyAdminWebuzo', $this->reflection->getNamespaceName());
    }

    /**
     * Test that getSettings does not throw when invoked with a stub subject.
     */
    public function testGetSettingsDoesNotThrow(): void
    {
        $settings = new class {
        };
        $event = new GenericEvent($settings);

        Plugin::getSettings($event);

        // If we reach this point, the method completed without error
        $this->assertTrue(true);
    }

    /**
     * Test that all hook callbacks reference callable static methods.
     */
    public function testAllHookCallbacksAreCallable(): void
    {
        $hooks = Plugin::getHooks();
        foreach ($hooks as $hookName => $callback) {
            $this->assertTrue(
                is_callable($callback),
                "Hook callback for '{$hookName}' should be callable"
            );
        }
    }
}

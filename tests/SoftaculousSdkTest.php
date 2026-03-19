<?php

declare(strict_types=1);

namespace Detain\MyAdminWebuzo\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Softaculous_SDK;
use Softaculous_API;

/**
 * Tests for the Softaculous_SDK and Softaculous_API classes.
 *
 * Validates class structure, properties, method signatures,
 * and backward-compatibility alias.
 */
class SoftaculousSdkTest extends TestCase
{
    /**
     * @var ReflectionClass<Softaculous_SDK>
     */
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        require_once dirname(__DIR__) . '/src/sdk.php';
        $this->reflection = new ReflectionClass(Softaculous_SDK::class);
    }

    /**
     * Test that the Softaculous_SDK class exists.
     */
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(Softaculous_SDK::class));
    }

    /**
     * Test that the Softaculous_SDK can be instantiated.
     */
    public function testCanBeInstantiated(): void
    {
        $sdk = new Softaculous_SDK();
        $this->assertInstanceOf(Softaculous_SDK::class, $sdk);
    }

    /**
     * Test that the SOFTACULOUS constant is defined.
     */
    public function testSoftaculousConstantDefined(): void
    {
        $this->assertTrue(defined('SOFTACULOUS'));
        $this->assertSame(1, SOFTACULOUS);
    }

    /**
     * Test that default property values are correct.
     */
    public function testDefaultPropertyValues(): void
    {
        $sdk = new Softaculous_SDK();
        $this->assertSame('', $sdk->login);
        $this->assertSame(0, $sdk->debug);
        $this->assertSame([], $sdk->error);
        $this->assertSame([], $sdk->data);
        $this->assertSame([], $sdk->scripts);
        $this->assertSame([], $sdk->iscripts);
        $this->assertSame('serialize', $sdk->format);
    }

    /**
     * Test that the cookie property defaults to null.
     */
    public function testCookiePropertyDefaultsToNull(): void
    {
        $sdk = new Softaculous_SDK();
        $this->assertNull($sdk->cookie);
    }

    /**
     * Test that all expected public properties exist.
     */
    public function testPublicPropertiesExist(): void
    {
        $expectedProps = ['login', 'debug', 'error', 'data', 'scripts', 'iscripts', 'cookie', 'format'];
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

    /**
     * Test that all expected public methods exist.
     */
    public function testPublicMethodsExist(): void
    {
        $expectedMethods = [
            'curl',
            'curl_call',
            'curl_unserialize',
            'install',
            'import',
            'upgrade',
            'restore',
            'remove',
            'backup',
            'remove_backup',
            'download_backup',
            'installations',
            'list_scripts',
            'list_backups',
            'list_installed_scripts',
            'r_print',
        ];
        foreach ($expectedMethods as $method) {
            $this->assertTrue(
                $this->reflection->hasMethod($method),
                "Method {$method}() should exist"
            );
            $this->assertTrue(
                $this->reflection->getMethod($method)->isPublic(),
                "Method {$method}() should be public"
            );
        }
    }

    /**
     * Test the curl method signature.
     */
    public function testCurlMethodSignature(): void
    {
        $method = $this->reflection->getMethod('curl');
        $params = $method->getParameters();
        $this->assertGreaterThanOrEqual(1, count($params));
        $this->assertSame('url', $params[0]->getName());
        $this->assertSame('post', $params[1]->getName());
        $this->assertTrue($params[1]->isDefaultValueAvailable());
        $this->assertSame('cookies', $params[2]->getName());
        $this->assertSame('header', $params[3]->getName());
    }

    /**
     * Test the curl_call method signature.
     */
    public function testCurlCallMethodSignature(): void
    {
        $method = $this->reflection->getMethod('curl_call');
        $params = $method->getParameters();
        $this->assertCount(2, $params);
        $this->assertSame('act', $params[0]->getName());
        $this->assertSame('post', $params[1]->getName());
        $this->assertTrue($params[1]->isDefaultValueAvailable());
    }

    /**
     * Test the install method signature.
     */
    public function testInstallMethodSignature(): void
    {
        $method = $this->reflection->getMethod('install');
        $params = $method->getParameters();
        $this->assertCount(3, $params);
        $this->assertSame('sid', $params[0]->getName());
        $this->assertSame('data', $params[1]->getName());
        $this->assertSame('autoinstall', $params[2]->getName());
    }

    /**
     * Test the upgrade method signature.
     */
    public function testUpgradeMethodSignature(): void
    {
        $method = $this->reflection->getMethod('upgrade');
        $params = $method->getParameters();
        $this->assertCount(2, $params);
        $this->assertSame('insid', $params[0]->getName());
        $this->assertSame('data', $params[1]->getName());
    }

    /**
     * Test the restore method signature.
     */
    public function testRestoreMethodSignature(): void
    {
        $method = $this->reflection->getMethod('restore');
        $params = $method->getParameters();
        $this->assertCount(2, $params);
        $this->assertSame('name', $params[0]->getName());
        $this->assertSame('data', $params[1]->getName());
    }

    /**
     * Test the remove method signature.
     */
    public function testRemoveMethodSignature(): void
    {
        $method = $this->reflection->getMethod('remove');
        $params = $method->getParameters();
        $this->assertCount(2, $params);
        $this->assertSame('insid', $params[0]->getName());
    }

    /**
     * Test the backup method signature.
     */
    public function testBackupMethodSignature(): void
    {
        $method = $this->reflection->getMethod('backup');
        $params = $method->getParameters();
        $this->assertCount(2, $params);
        $this->assertSame('insid', $params[0]->getName());
    }

    /**
     * Test the download_backup method signature.
     */
    public function testDownloadBackupMethodSignature(): void
    {
        $method = $this->reflection->getMethod('download_backup');
        $params = $method->getParameters();
        $this->assertCount(2, $params);
        $this->assertSame('download_file', $params[0]->getName());
        $this->assertSame('path', $params[1]->getName());
        $this->assertTrue($params[1]->isDefaultValueAvailable());
        $this->assertNull($params[1]->getDefaultValue());
    }

    /**
     * Test the installations method signature.
     */
    public function testInstallationsMethodSignature(): void
    {
        $method = $this->reflection->getMethod('installations');
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('showupdates', $params[0]->getName());
        $this->assertTrue($params[0]->isDefaultValueAvailable());
        $this->assertFalse($params[0]->getDefaultValue());
    }

    /**
     * Test that Softaculous_API extends Softaculous_SDK for backward compatibility.
     */
    public function testSoftaculousApiExtendsSDK(): void
    {
        $this->assertTrue(class_exists(Softaculous_API::class));
        $apiReflection = new ReflectionClass(Softaculous_API::class);
        $this->assertTrue($apiReflection->isSubclassOf(Softaculous_SDK::class));
    }

    /**
     * Test that error property can be populated.
     */
    public function testErrorArrayCanBePopulated(): void
    {
        $sdk = new Softaculous_SDK();
        $sdk->error[] = 'Test error';
        $this->assertCount(1, $sdk->error);
        $this->assertSame('Test error', $sdk->error[0]);
    }

    /**
     * Test that login URL can be set.
     */
    public function testLoginCanBeSet(): void
    {
        $sdk = new Softaculous_SDK();
        $sdk->login = 'https://user:pass@example.com:2083/softaculous/index.live.php';
        $this->assertStringContainsString('example.com', $sdk->login);
    }

    /**
     * Test that format property can be changed.
     */
    public function testFormatCanBeChanged(): void
    {
        $sdk = new Softaculous_SDK();
        $sdk->format = 'json';
        $this->assertSame('json', $sdk->format);
    }

    /**
     * Test install returns false when script is not found.
     */
    public function testInstallReturnsFalseWhenScriptNotFound(): void
    {
        $sdk = new Softaculous_SDK();
        // Populate iscripts to avoid curl call but without the target script
        $sdk->iscripts = [1 => ['name' => 'WordPress', 'type' => 'php']];

        $result = $sdk->install(999);
        $this->assertFalse($result);
        $this->assertContains('Script Not Found', $sdk->error);
    }

    /**
     * Test import returns false when script is not found.
     */
    public function testImportReturnsFalseWhenScriptNotFound(): void
    {
        $sdk = new Softaculous_SDK();
        $sdk->iscripts = [1 => ['name' => 'WordPress', 'type' => 'php']];

        $result = $sdk->import(999);
        $this->assertFalse($result);
        $this->assertContains('Script Not Found', $sdk->error);
    }

    /**
     * Test list_scripts returns true when scripts are already loaded.
     */
    public function testListScriptsReturnsTrueWhenAlreadyLoaded(): void
    {
        $sdk = new Softaculous_SDK();
        $sdk->scripts = [26 => ['name' => 'WordPress']];

        $result = $sdk->list_scripts();
        $this->assertTrue($result);
    }

    /**
     * Test list_installed_scripts returns true when already loaded.
     */
    public function testListInstalledScriptsReturnsTrueWhenAlreadyLoaded(): void
    {
        $sdk = new Softaculous_SDK();
        $sdk->iscripts = [26 => ['name' => 'WordPress', 'type' => 'php']];

        $result = $sdk->list_installed_scripts();
        $this->assertTrue($result);
    }
}

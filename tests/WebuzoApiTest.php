<?php

declare(strict_types=1);

namespace Detain\MyAdminWebuzo\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Webuzo_API;
use Softaculous_API;

/**
 * Tests for the Webuzo_API class.
 *
 * Validates class hierarchy, constructor, property defaults,
 * and method signatures for the Webuzo SDK wrapper.
 */
class WebuzoApiTest extends TestCase
{
    /**
     * @var ReflectionClass<Webuzo_API>
     */
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        require_once dirname(__DIR__) . '/src/webuzo_sdk.php';
        $this->reflection = new ReflectionClass(Webuzo_API::class);
    }

    /**
     * Test that Webuzo_API class exists.
     */
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(Webuzo_API::class));
    }

    /**
     * Test that Webuzo_API extends Softaculous_API.
     */
    public function testExtendsCorrectParent(): void
    {
        $this->assertTrue($this->reflection->isSubclassOf(Softaculous_API::class));
    }

    /**
     * Test that the WEBUZO constant is defined.
     */
    public function testWebuzoConstantDefined(): void
    {
        $this->assertTrue(defined('WEBUZO'));
        $this->assertSame(1, WEBUZO);
    }

    /**
     * Test that the constructor sets the login URL.
     */
    public function testConstructorSetsLoginUrl(): void
    {
        $api = new Webuzo_API('testuser', 'testpass', '192.168.1.1');
        $this->assertSame('https://testuser:testpass@192.168.1.1:2003/index.php', $api->login);
    }

    /**
     * Test that the constructor works with empty parameters.
     */
    public function testConstructorWithEmptyParams(): void
    {
        $api = new Webuzo_API();
        $this->assertSame('https://:@:2003/index.php', $api->login);
    }

    /**
     * Test that default property values are correct.
     */
    public function testDefaultPropertyValues(): void
    {
        $api = new Webuzo_API('u', 'p', 'h');
        $this->assertSame(0, $api->debug);
        $this->assertSame([], $api->error);
        $this->assertSame([], $api->data);
        $this->assertSame([], $api->apps);
        $this->assertSame([], $api->installed_apps);
    }

    /**
     * Test that the apps property exists and is public.
     */
    public function testAppsPropertyExists(): void
    {
        $prop = $this->reflection->getProperty('apps');
        $this->assertTrue($prop->isPublic());
    }

    /**
     * Test that the installed_apps property exists and is public.
     */
    public function testInstalledAppsPropertyExists(): void
    {
        $prop = $this->reflection->getProperty('installed_apps');
        $this->assertTrue($prop->isPublic());
    }

    /**
     * Test that all expected Webuzo-specific methods exist.
     */
    public function testWebuzoSpecificMethodsExist(): void
    {
        $methods = [
            'webuzo_configure',
            'install_app',
            'remove_app',
            'list_apps',
            'list_installed_apps',
            'list_services',
            'chk_error',
            'list_domains',
            'add_domain',
            'delete_domain',
            'change_password',
            'change_fileman_pwd',
            'change_tomcat_pwd',
            'list_ftpuser',
            'add_ftpuser',
            'edit_ftpuser',
            'change_ftpuser_pass',
            'delete_ftpuser',
            'list_ftp_connections',
            'delete_ftp_connection',
            'list_database',
            'add_database',
            'delete_database',
            'list_db_user',
            'add_db_user',
            'delete_db_user',
            'set_privileges',
            'edit_settings',
            'manage_service',
            'manage_suphp',
            'enable_proxy',
            'disable_proxy',
            'list_dns_record',
            'add_dns_record',
            'edit_dns_record',
            'delete_dns_record',
            'list_cron',
            'add_cron',
        ];
        foreach ($methods as $method) {
            $this->assertTrue(
                $this->reflection->hasMethod($method),
                "Method {$method}() should exist on Webuzo_API"
            );
        }
    }

    /**
     * Test webuzo_configure method signature.
     */
    public function testWebuzoCofigureSignature(): void
    {
        $method = $this->reflection->getMethod('webuzo_configure');
        $params = $method->getParameters();
        $names = array_map(fn($p) => $p->getName(), $params);
        $this->assertContains('ip', $names);
        $this->assertContains('user', $names);
        $this->assertContains('email', $names);
        $this->assertContains('pass', $names);
        $this->assertContains('host', $names);
    }

    /**
     * Test add_domain method signature.
     */
    public function testAddDomainSignature(): void
    {
        $method = $this->reflection->getMethod('add_domain');
        $params = $method->getParameters();
        $this->assertSame('domain', $params[0]->getName());
        $this->assertSame('domainpath', $params[1]->getName());
        $this->assertTrue($params[1]->isDefaultValueAvailable());
        $this->assertSame('', $params[1]->getDefaultValue());
    }

    /**
     * Test delete_domain method signature.
     */
    public function testDeleteDomainSignature(): void
    {
        $method = $this->reflection->getMethod('delete_domain');
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('domain', $params[0]->getName());
    }

    /**
     * Test change_password method signature.
     */
    public function testChangePasswordSignature(): void
    {
        $method = $this->reflection->getMethod('change_password');
        $params = $method->getParameters();
        $this->assertSame('pass', $params[0]->getName());
        $this->assertSame('user', $params[1]->getName());
        $this->assertTrue($params[1]->isDefaultValueAvailable());
        $this->assertSame('', $params[1]->getDefaultValue());
    }

    /**
     * Test add_ftpuser method signature.
     */
    public function testAddFtpuserSignature(): void
    {
        $method = $this->reflection->getMethod('add_ftpuser');
        $params = $method->getParameters();
        $this->assertSame('user', $params[0]->getName());
        $this->assertSame('pass', $params[1]->getName());
        $this->assertSame('directory', $params[2]->getName());
        $this->assertSame('quota_limit', $params[3]->getName());
        $this->assertTrue($params[3]->isDefaultValueAvailable());
    }

    /**
     * Test install_app returns false when app is not found.
     */
    public function testInstallAppReturnsFalseWhenAppNotFound(): void
    {
        $api = new Webuzo_API('u', 'p', 'h');
        // Pre-load apps so it doesn't try to curl
        $api->apps = [100 => ['name' => 'SomeApp']];

        $result = $api->install_app(999);
        $this->assertFalse($result);
        $this->assertContains('App Not Found', $api->error);
    }

    /**
     * Test remove_app returns false when app is not found.
     */
    public function testRemoveAppReturnsFalseWhenAppNotFound(): void
    {
        $api = new Webuzo_API('u', 'p', 'h');
        // Pre-load installed_apps so it doesn't try to curl
        $api->installed_apps = [100 => ['name' => 'SomeApp']];

        $result = $api->remove_app(999);
        $this->assertFalse($result);
        $this->assertContains('App Not Found', $api->error);
    }

    /**
     * Test list_apps returns true when apps are already loaded.
     */
    public function testListAppsReturnsTrueWhenAlreadyLoaded(): void
    {
        $api = new Webuzo_API('u', 'p', 'h');
        $api->apps = [100 => ['name' => 'Apache']];

        $result = $api->list_apps();
        $this->assertTrue($result);
    }

    /**
     * Test list_installed_apps returns cached data.
     */
    public function testListInstalledAppsReturnsCached(): void
    {
        $api = new Webuzo_API('u', 'p', 'h');
        $expected = [100 => ['name' => 'Apache', 'version' => '2.4']];
        $api->installed_apps = $expected;

        $result = $api->list_installed_apps();
        $this->assertSame($expected, $result);
    }

    /**
     * Test manage_suphp method signature accepts an action parameter.
     */
    public function testManageSuphpSignature(): void
    {
        $method = $this->reflection->getMethod('manage_suphp');
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('action', $params[0]->getName());
    }

    /**
     * Test enable_proxy method signature.
     */
    public function testEnableProxySignature(): void
    {
        $method = $this->reflection->getMethod('enable_proxy');
        $params = $method->getParameters();
        $this->assertCount(3, $params);
        $this->assertSame('port', $params[0]->getName());
        $this->assertSame('htaccess', $params[1]->getName());
        $this->assertSame('proxy_server', $params[2]->getName());
    }

    /**
     * Test set_privileges method signature.
     */
    public function testSetPrivilegesSignature(): void
    {
        $method = $this->reflection->getMethod('set_privileges');
        $params = $method->getParameters();
        $this->assertCount(4, $params);
        $this->assertSame('database', $params[0]->getName());
        $this->assertSame('db_user', $params[1]->getName());
        $this->assertSame('host', $params[2]->getName());
        $this->assertSame('prilist', $params[3]->getName());
    }

    /**
     * Test that inherited methods from Softaculous_SDK are accessible.
     */
    public function testInheritedMethodsAccessible(): void
    {
        $api = new Webuzo_API('u', 'p', 'h');
        $this->assertTrue(method_exists($api, 'curl'));
        $this->assertTrue(method_exists($api, 'curl_call'));
        $this->assertTrue(method_exists($api, 'install'));
        $this->assertTrue(method_exists($api, 'list_scripts'));
    }
}

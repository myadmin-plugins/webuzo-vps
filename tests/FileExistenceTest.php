<?php

declare(strict_types=1);

namespace Detain\MyAdminWebuzo\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Tests verifying all expected source files exist in the package.
 *
 * Ensures the package distribution is complete.
 */
class FileExistenceTest extends TestCase
{
    /**
     * @var string
     */
    private string $srcDir;

    protected function setUp(): void
    {
        $this->srcDir = dirname(__DIR__) . '/src';
    }

    /**
     * Data provider for source file existence checks.
     *
     * @return array<string, array{0: string}>
     */
    public function sourceFileProvider(): array
    {
        return [
            'Plugin.php' => ['Plugin.php'],
            'sdk.php' => ['sdk.php'],
            'webuzo_sdk.php' => ['webuzo_sdk.php'],
            'webuzo.functions.inc.php' => ['webuzo.functions.inc.php'],
            'webuzo_configure.php' => ['webuzo_configure.php'],
            'webuzo_scripts.php' => ['webuzo_scripts.php'],
            'webuzo_edit_installation.php' => ['webuzo_edit_installation.php'],
            'webuzo_add_domain.php' => ['webuzo_add_domain.php'],
            'webuzo_remove_domain.php' => ['webuzo_remove_domain.php'],
            'webuzo_list_domains.php' => ['webuzo_list_domains.php'],
            'webuzo_list_backups.php' => ['webuzo_list_backups.php'],
            'webuzo_list_installed_scripts.php' => ['webuzo_list_installed_scripts.php'],
            'webuzo_list_installed_sysapps.php' => ['webuzo_list_installed_sysapps.php'],
            'webuzo_list_sysapps.php' => ['webuzo_list_sysapps.php'],
            'webuzo_install_sysapp.php' => ['webuzo_install_sysapp.php'],
            'webuzo_import_script.php' => ['webuzo_import_script.php'],
            'webuzo_remove_script.php' => ['webuzo_remove_script.php'],
            'webuzo_view_script.php' => ['webuzo_view_script.php'],
            'webuzo_view_sysapps.php' => ['webuzo_view_sysapps.php'],
            'webuzo_randomPassword.php' => ['webuzo_randomPassword.php'],
            'webuzo_update_logo.php' => ['webuzo_update_logo.php'],
        ];
    }

    /**
     * Test that each expected source file exists.
     *
     * @dataProvider sourceFileProvider
     */
    public function testSourceFileExists(string $filename): void
    {
        $this->assertFileExists(
            $this->srcDir . '/' . $filename,
            "Source file {$filename} should exist in src/"
        );
    }

    /**
     * Test that composer.json exists.
     */
    public function testComposerJsonExists(): void
    {
        $this->assertFileExists(dirname(__DIR__) . '/composer.json');
    }

    /**
     * Test that README.md exists.
     */
    public function testReadmeExists(): void
    {
        $this->assertFileExists(dirname(__DIR__) . '/README.md');
    }
}

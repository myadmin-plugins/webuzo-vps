<?php

declare(strict_types=1);

namespace Detain\MyAdminWebuzo\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Tests for the pure functions defined in webuzo.functions.inc.php.
 *
 * Covers webuzo_format_units_size which is a pure function suitable
 * for direct unit testing.
 */
class WebuzoFunctionsTest extends TestCase
{
    /**
     * Load the functions file once.
     */
    public static function setUpBeforeClass(): void
    {
        require_once dirname(__DIR__) . '/src/webuzo.functions.inc.php';
    }

    /**
     * Test that webuzo_format_units_size function exists.
     */
    public function testFormatUnitsSizeFunctionExists(): void
    {
        $this->assertTrue(function_exists('webuzo_format_units_size'));
    }

    /**
     * Test formatting zero bytes.
     */
    public function testFormatZeroBytes(): void
    {
        $this->assertSame('0 bytes', webuzo_format_units_size(0));
    }

    /**
     * Test formatting exactly one byte.
     */
    public function testFormatOneByte(): void
    {
        $this->assertSame('1 byte', webuzo_format_units_size(1));
    }

    /**
     * Test formatting small number of bytes.
     */
    public function testFormatBytes(): void
    {
        $result = webuzo_format_units_size(500);
        $this->assertStringContainsString('bytes', $result);
        $this->assertStringContainsString('500', $result);
    }

    /**
     * Test formatting kilobytes.
     */
    public function testFormatKilobytes(): void
    {
        $result = webuzo_format_units_size(1024);
        $this->assertStringContainsString('KB', $result);
        $this->assertSame('1.00 KB', $result);
    }

    /**
     * Test formatting megabytes.
     */
    public function testFormatMegabytes(): void
    {
        $result = webuzo_format_units_size(1048576);
        $this->assertStringContainsString('MB', $result);
        $this->assertSame('1.00 MB', $result);
    }

    /**
     * Test formatting gigabytes.
     */
    public function testFormatGigabytes(): void
    {
        $result = webuzo_format_units_size(1073741824);
        $this->assertStringContainsString('GB', $result);
        $this->assertSame('1.00 GB', $result);
    }

    /**
     * Test formatting a value between KB and MB.
     */
    public function testFormatMixedKilobytes(): void
    {
        $result = webuzo_format_units_size(512000);
        $this->assertStringContainsString('KB', $result);
        $this->assertSame('500.00 KB', $result);
    }

    /**
     * Test formatting a large GB value.
     */
    public function testFormatLargeGigabytes(): void
    {
        $result = webuzo_format_units_size(5368709120);
        $this->assertStringContainsString('GB', $result);
        $this->assertSame('5.00 GB', $result);
    }

    /**
     * Test formatting 2 bytes.
     */
    public function testFormatTwoBytes(): void
    {
        $result = webuzo_format_units_size(2);
        $this->assertSame('2 bytes', $result);
    }

    /**
     * Test formatting 1023 bytes stays in bytes range.
     */
    public function testFormatJustUnderKilobyte(): void
    {
        $result = webuzo_format_units_size(1023);
        $this->assertStringContainsString('bytes', $result);
    }

    /**
     * Test that webuzo_randomPassword function exists.
     */
    public function testRandomPasswordFunctionExists(): void
    {
        require_once dirname(__DIR__) . '/src/webuzo_randomPassword.php';
        $this->assertTrue(function_exists('webuzo_randomPassword'));
    }

    /**
     * Test that webuzo_randomPassword returns correct default length.
     */
    public function testRandomPasswordDefaultLength(): void
    {
        $password = webuzo_randomPassword();
        $this->assertSame(8, mb_strlen($password));
    }

    /**
     * Test that webuzo_randomPassword returns specified length.
     */
    public function testRandomPasswordCustomLength(): void
    {
        $password = webuzo_randomPassword(16);
        $this->assertSame(16, mb_strlen($password));
    }

    /**
     * Test that webuzo_randomPassword returns only alphanumeric characters.
     */
    public function testRandomPasswordOnlyAlphanumeric(): void
    {
        $password = webuzo_randomPassword(100);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $password);
    }

    /**
     * Test that webuzo_randomPassword returns different values (not always the same).
     */
    public function testRandomPasswordIsRandom(): void
    {
        $passwords = [];
        for ($i = 0; $i < 10; $i++) {
            $passwords[] = webuzo_randomPassword(16);
        }
        // At least 2 unique values out of 10 runs
        $unique = array_unique($passwords);
        $this->assertGreaterThan(1, count($unique));
    }

    /**
     * Test that webuzo_randomPassword with length zero returns empty string.
     */
    public function testRandomPasswordZeroLength(): void
    {
        $password = webuzo_randomPassword(0);
        $this->assertSame('', $password);
    }

    /**
     * Test that webuzo_randomPassword with length 1 returns a single character.
     */
    public function testRandomPasswordLengthOne(): void
    {
        $password = webuzo_randomPassword(1);
        $this->assertSame(1, mb_strlen($password));
    }
}

<?php

declare(strict_types=1);

namespace Detain\MyAdminWebuzo\Tests;

use Detain\MyAdminWebuzo\WebuzoSecrets;
use MyAdmin\Plugins\Testing\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * The four Webuzo readers take the admin password from the `webuzo_pass`
 * history row through WebuzoSecrets::password() (MyAdmin plan_2way §5.0.2):
 * plaintext unchanged, a SecretBox envelope opened by core, never sent raw.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class WebuzoSecretsTest extends TestCase
{
    private static function row($new): array
    {
        return ['history_id' => 7, 'history_section' => 'vps', 'history_type' => 'webuzo_pass', 'history_new_value' => $new, 'history_old_value' => 'Webuzo Details', 'history_owner' => 5];
    }

    public function testPlaintextComesBackUnchangedThroughCoresReader(): void
    {
        Bootstrap::init(['module' => 'vps']);
        $this->assertTrue(class_exists('MyAdmin\\Security\\ServiceSecrets'));
        $this->assertSame("Wz'Pw\\1", WebuzoSecrets::password(self::row("Wz'Pw\\1")));
        $this->assertSame('', WebuzoSecrets::password(self::row('')));
    }

    public function testAnEnvelopeIsNeverHandedOnRaw(): void
    {
        Bootstrap::init(['module' => 'vps']);
        $this->expectException(\LogicException::class);
        WebuzoSecrets::password(self::row('S1.k1.' . str_repeat('A', 60)));
    }

    public function testWithoutCoresClassTheStoredValueIsUsedAsBefore(): void
    {
        $this->assertFalse(class_exists('MyAdmin\\Security\\ServiceSecrets'));
        $this->assertSame('Old-Pw', WebuzoSecrets::password(self::row('Old-Pw')));
        $this->assertNull(WebuzoSecrets::password([]));
    }

    public function testEveryReaderGoesThroughIt(): void
    {
        foreach (['webuzo_scripts.php' => '$webuzo_password', 'webuzo_install_sysapp.php' => '$pass', 'webuzo_edit_installation.php' => '$pass', 'webuzo_add_domain.php' => '$pass'] as $file => $var) {
            $src = (string) file_get_contents(dirname(__DIR__) . '/src/' . $file);
            $this->assertStringContainsString("{$var} = \\Detain\\MyAdminWebuzo\\WebuzoSecrets::password(\$db->Record);", $src, $file);
            $this->assertStringNotContainsString("= \$db->Record['history_new_value'];", $src, $file);
        }
        $this->assertStringNotContainsString('{$pass}', (string) file_get_contents(dirname(__DIR__) . '/src/webuzo_configure.php'), 'the configure log line no longer carries the password');
    }
}

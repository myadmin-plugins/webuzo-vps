<?php

namespace Detain\MyAdminWebuzo;

/**
 * The Webuzo admin password a VPS's `webuzo_pass` history row holds, for the
 * Webuzo API calls (MyAdmin plan_2way §5.0.2, SecretBox).
 *
 * Through core's ServiceSecrets::readHistoryRow(): plaintext comes back exactly
 * as stored, and only a SecretBox envelope is opened (it throws when one will
 * not open, so an envelope is never sent to Webuzo as a password). A core tree
 * without the class gets the stored value, as before.
 */
class WebuzoSecrets
{
    /**
     * @param array<string,mixed> $row the selected history_log row (select *)
     * @return mixed history_new_value, opened
     */
    public static function password(array $row)
    {
        if (!class_exists('MyAdmin\\Security\\ServiceSecrets')) {
            return $row['history_new_value'] ?? null;
        }
        return \MyAdmin\Security\ServiceSecrets::readHistoryRow($row)['history_new_value'];
    }
}

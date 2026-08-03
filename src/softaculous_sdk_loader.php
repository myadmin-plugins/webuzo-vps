<?php

/**
 * Locates and loads the Softaculous SDK (Softaculous_SDK / Softaculous_API).
 *
 * The canonical copy of this SDK ships with the MyAdmin core tree at
 * include/webhosting/softaculous/sdk.php. When this plugin is installed the
 * normal way (composer, into <core>/vendor/detain/myadmin-webuzo-vps) that copy
 * lives four directories above src/ and is the one production actually runs
 * against, so it is always preferred. When the package is used outside of a core
 * tree -- a bare git clone, a CI job, a phpunit run started from any other
 * directory -- that path does not exist and we fall back to the copy bundled in
 * this package.
 *
 * This replaces a bare `include_once(dirname(__FILE__).'/../../../../include/...')`
 * in webuzo_sdk.php. That form had two problems: it only ever resolved inside a
 * core checkout, and because it used include_once (not require_once) a missing
 * core tree produced nothing but a warning, after which the file kept going and
 * failed much later with a confusing "Class Softaculous_API not found".
 *
 * Loading is guarded on Softaculous_API rather than Softaculous_SDK because
 * webuzo_sdk.php extends the former; both classes live in the same file.
 */
if (!class_exists('Softaculous_API', false)) {
    $softaculousSdkCandidates = [
        // canonical copy shipped by the MyAdmin core tree
        dirname(__DIR__, 4) . '/include/webhosting/softaculous/sdk.php',
        // copy bundled with this package, for standalone/CI use
        __DIR__ . '/sdk.php',
    ];

    $softaculousSdkPath = null;
    foreach ($softaculousSdkCandidates as $softaculousSdkCandidate) {
        if (is_file($softaculousSdkCandidate)) {
            $softaculousSdkPath = realpath($softaculousSdkCandidate);
            break;
        }
    }

    if ($softaculousSdkPath === null) {
        throw new \RuntimeException(
            'Unable to locate the Softaculous SDK. Looked in: '
            . implode(', ', $softaculousSdkCandidates)
        );
    }

    require_once $softaculousSdkPath;

    unset($softaculousSdkCandidates, $softaculousSdkCandidate, $softaculousSdkPath);
}

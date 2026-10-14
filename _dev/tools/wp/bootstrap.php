<?php
/**
 * Loads WordPress for CLI scripts, then runs the script passed as the first argument.
 * Called by _dev/tools/wp/run.sh — run that, not this file.
 */

if (PHP_SAPI !== 'cli') {
    exit;
}

$_SERVER['HTTP_HOST']   = getenv('WP_HOST') ?: 'localhost';
$_SERVER['REQUEST_URI'] = '/';

require getenv('WP_ROOT') . '/wp-load.php';

require $argv[1];
echo PHP_EOL;

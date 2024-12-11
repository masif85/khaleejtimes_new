<?php

/**
 * Plugin Name: Everyware Starter Package Catalyst
 * Description: Kickstart Everyware Starter Package
 * Author: Infomaker Scandinavia AB
 */

$dirs = glob(WPMU_PLUGIN_DIR . '/*', GLOB_ONLYDIR);
$whiteListedFiles = [
    'bootstrap.php',
    'index.php',
];

/**
 * Will look for a file with the same name as the plugin folder or a filename declared in $WhitelistedFiles.
 */
foreach ($dirs as $dir) {
    $fileBase = $dir . DIRECTORY_SEPARATOR;
    $pluginFile = $fileBase . basename($dir) . '.php';

    if (file_exists($pluginFile)) {
        require $pluginFile;
        continue;
    }

    foreach ($whiteListedFiles as $file) {
        $pluginFile = $fileBase . $file;
        if (file_exists($pluginFile)) {
            require $pluginFile;
            continue;
        }
    }
}

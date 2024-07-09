<?php

if (!class_exists('\ImportWP\Common\AddonAPI\Addon')) {

    $autoload = __DIR__ . '/importwp-api/autoload.php';
    if (!file_exists($autoload)) {
        die('Missing ImportWP API');
    }

    require_once $autoload;
}

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/pods-importer-addon.php';
require_once __DIR__ . '/pods-exporter-addon.php';

// should be a better way to load this
new PodsImporterAddon();
new PodsExporterAddon();

<?php

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/pods-importer-addon.php';
require_once __DIR__ . '/pods-exporter-addon.php';

// should be a better way to load this
new PodsImporterAddon();
new PodsExporterAddon();

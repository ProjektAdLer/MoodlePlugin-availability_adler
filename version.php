<?php

/**
 * @package     availability_adler
 * @copyright   2023 Markus Heck <markus.heck@hs-kempten.de>
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2023110800;
$plugin->requires = 2021051712.05;  // Moodle version
$plugin->component = 'availability_adler';
$plugin->maturity = MATURITY_ALPHA;
$plugin->release = '2.0.0-rc.1';
$plugin->dependencies = array(
    'local_adler' => ANY_VERSION,   // The Foo activity must be present (any version).
);

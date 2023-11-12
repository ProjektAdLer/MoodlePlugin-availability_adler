<?php

/**
 * @package     availability_adler
 * @copyright   2023 Markus Heck <markus.heck@hs-kempten.de>
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2023111200;
$plugin->requires = 2022112800;  // Moodle version
$plugin->component = 'availability_adler';
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '2.0.0';
$plugin->dependencies = array(
    'local_adler' => '2.0.0',   // The Foo activity must be present (any version).
);

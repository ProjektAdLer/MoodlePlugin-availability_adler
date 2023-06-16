<?php

/**
 * @package     availability_adler
 * @copyright   2023 Markus Heck <markus.heck@hs-kempten.de>
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2023061600;
$plugin->requires = 2021051712.05;  // Moodle version
$plugin->component = 'availability_adler';
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '1.0.0';
$plugin->dependencies = array(
    'local_adler' => ANY_VERSION,   // The Foo activity must be present (any version).
);

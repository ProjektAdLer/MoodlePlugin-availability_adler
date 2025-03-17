<?php

/**
 * @package     availability_adler
 * @copyright   2023 Markus Heck <markus.heck@hs-kempten.de>
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2025031600;
$plugin->requires = 2024042200;  // Moodle version
$plugin->component = 'availability_adler';
$plugin->release = '4.1.0';
$plugin->maturity = MATURITY_STABLE;
$plugin->dependencies = array(
    'local_logging' => ANY_VERSION,
);
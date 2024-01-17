<?php

/**
 * @package     availability_adler
 * @copyright   2023 Markus Heck <markus.heck@hs-kempten.de>
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2024011703;
$plugin->requires = 2022112800;  // Moodle version
$plugin->component = 'availability_adler';
$plugin->release = '4.0.0-dev';
$plugin->maturity = MATURITY_ALPHA;
$plugin->dependencies = array(
    'local_logging' => ANY_VERSION,
);
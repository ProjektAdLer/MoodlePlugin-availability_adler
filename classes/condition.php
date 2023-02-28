<?php

namespace availability_adler;


use core_availability\condition as availability_condition;
use core_availability\info;
use core_plugin_manager;

class condition extends availability_condition {
    # https://moodledev.io/docs/apis/plugintypes/availability
    protected string $condition;

    public function __construct($structure) {
        // TODO: validate structure
        $this->condition = $structure->condition;
    }

    public function is_available($not, info $info, $grabthelot, $userid) {
        // check if local_adler is available
        $plugins = core_plugin_manager::instance()->get_enabled_plugins('local_adler');
        if (!array_key_exists('local_adler', $plugins)) {
            debugging('local_adler is not available', E_WARNING);
            return true;
        }

        // local_adler is available
        debugging('Not implemented', E_ERROR);
        return false;
    }

    public function get_description($full, $not, info $info) {
        // TODO: use get_string
        return 'Requires previous rooms to be completed according to the rule: ' . $this->condition;
    }

    protected function get_debug_string() {
        return 'Room condition: ' . $this->condition;
    }

    public function save() {
        return (object) [
            'type' => 'adler',
            'condition' => $this->condition,
        ];
    }
}
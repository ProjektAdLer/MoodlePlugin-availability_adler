<?php

namespace availability_adler;


use coding_exception;
use core_availability\condition as availability_condition;
use core_availability\info;
use core_plugin_manager;
use invalid_parameter_exception;

class condition extends availability_condition {
    # https://moodledev.io/docs/apis/plugintypes/availability
    protected string $condition;

    public function __construct($structure) {
        if (isset($structure->condition)) {
            // TODO: validate structure
            $this->condition = $structure->condition;
        } else {
            throw new coding_exception('adler statement not set');
        }
    }

    protected function evaluate_room_requirements($statement, $userid): bool {
        // search for brackets
        for ($i = 0; $i < strlen($statement); $i++) {
            if ($statement[$i] == '(') {
                $start = $i;
                $end = $i;
                $depth = 1;
                for ($j = $i + 1; $j < strlen($statement); $j++) {
                    if ($statement[$j] == '(') {
                        $depth++;
                    } else if ($statement[$j] == ')') {
                        $depth--;
                    }
                    if ($depth == 0) {
                        $end = $j;
                        break;
                    }
                }
                $substatement = substr($statement, $start + 1, $end - $start - 1);
                $result = $this->evaluate_room_requirements($substatement, $userid)? 't' : 'f';
                $statement = substr($statement, 0, $start) . $result . substr($statement, $end + 1);
                $i = $start;
            }
        }

        // Search for AND and OR following the rule "AND before OR"
        // search for AND (^)
        for ($i = 0; $i < strlen($statement); $i++) {
            if ($statement[$i] == '^') {
                $left = substr($statement, 0, $i);
                $right = substr($statement, $i + 1);
                $statement = ($this->evaluate_room_requirements($left, $userid) == 't' && $this->evaluate_room_requirements($right, $userid) == 't')? 't' : 'f';
                break;
            }
        }
        // search for OR (v)
        for ($i = 0; $i < strlen($statement); $i++) {
            if ($statement[$i] == 'v') {
                $left = substr($statement, 0, $i);
                $right = substr($statement, $i + 1);
                $statement = ($this->evaluate_room_requirements($left, $userid) == 't'|| $this->evaluate_room_requirements($right, $userid)=='t')?'t':'f';
                break;
            }
        }

        // search for NOT (!)
        for ($i = 0; $i < strlen($statement); $i++) {
            if ($statement[$i] == '!') {
                $right = substr($statement, $i + 1);
                $statement = (!$this->evaluate_room_requirements($right, $userid)=='t')?'t':'f';
                break;
            }
        }

        // If this place is reached the statement should be only a number (room id)
        if (is_numeric($statement)) {
            $statement = $this->evaluate_room((int)$statement, $userid);
        } else if ($statement == 't' || $statement == 'f') {
            $statement = $statement == 't';
        } else {
            throw new invalid_parameter_exception('Invalid statement: ' . $statement);
        }

        return $statement;
    }

    protected function evaluate_room($roomid, $userid): bool {
        return false;
    }

    public function is_available($not, info $info, $grabthelot, $userid) {
        // check if local_adler is available
        $plugins = core_plugin_manager::instance()->get_installed_plugins('local');
        if (!array_key_exists('adler', $plugins)) {
            debugging('local_adler is not available', E_WARNING);
            $allow = true;
        } else {
            $allow = $this->evaluate_room_requirements($this->condition, $userid);
        }

        if ($not) {
            $allow = !$allow;
        }

        return $allow;
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

    // TODO: include_after_restore/... ???
}
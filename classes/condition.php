<?php

namespace availability_adler;

global $CFG;
require_once($CFG->dirroot . "/local/adler/classes/plugin_interface.php");


use base_logger;
use coding_exception;
use core_availability\condition as availability_condition;
use core_availability\info;
use core_plugin_manager;
use invalid_parameter_exception;
use local_adler\plugin_interface;
use moodle_exception;
use restore_dbops;


class condition extends availability_condition {
    use static_call_trait;

    protected string $condition;
    protected $core_plugin_manager_instance;

    /**
     * @throws coding_exception
     * @throws invalid_parameter_exception
     */
    public function __construct($structure) {
        if (isset($structure->condition)) {
            try {
                $this->evaluate_room_requirements($structure->condition, 0, true);
            } catch (invalid_parameter_exception $e) {
                throw new invalid_parameter_exception('Invalid condition: ' . $e->getMessage());
            }
            $this->condition = $structure->condition;
        } else {
            throw new coding_exception('adler condition not set');
        }

        $this->core_plugin_manager_instance = core_plugin_manager::instance();
    }

    /**
     * @param $statement
     * @param $userid
     * @param $validation_mode bool If set to true, this method is used to validate the condition. In this case,
     * the method will not call external methods. All calls to evaluate_room will be replaced with a "true" value.
     * @return bool
     * @throws invalid_parameter_exception
     */
    public function evaluate_room_requirements($statement, $userid, bool $validation_mode = false): bool {
        // TODO: protected
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
                $result = $this->evaluate_room_requirements($substatement, $userid, $validation_mode) ? 't' : 'f';
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
                $statement = ($this->evaluate_room_requirements($left, $userid, $validation_mode) == 't' && $this->evaluate_room_requirements($right, $userid) == 't') ? 't' : 'f';
                break;
            }
        }
        // search for OR (v)
        for ($i = 0; $i < strlen($statement); $i++) {
            if ($statement[$i] == 'v') {
                $left = substr($statement, 0, $i);
                $right = substr($statement, $i + 1);
                $statement = ($this->evaluate_room_requirements($left, $userid, $validation_mode) == 't' || $this->evaluate_room_requirements($right, $userid) == 't') ? 't' : 'f';
                break;
            }
        }

        // search for NOT (!)
        for ($i = 0; $i < strlen($statement); $i++) {
            if ($statement[$i] == '!') {
                $right = substr($statement, $i + 1);
                $statement = (!$this->evaluate_room_requirements($right, $userid, $validation_mode) == 't') ? 't' : 'f';
                break;
            }
        }

        // If this place is reached the statement should be only a number (room id)
        if (is_numeric($statement)) {
            $statement = $validation_mode || $this->evaluate_room((int)$statement, $userid);
        } else if ($statement == 't' || $statement == 'f') {
            $statement = $statement == 't';
        } else {
            throw new invalid_parameter_exception('Invalid statement: ' . $statement);
        }

        return $statement;
    }

    /**
     * @throws moodle_exception
     */
    protected function evaluate_room($roomid, $userid): bool {
        try {
            return $this->callStatic(plugin_interface::class, 'is_section_completed', $roomid, $userid);
        } catch (moodle_exception $e) {
            if ($e->errorcode == 'user_not_enrolled') {
                return false;
            } else {
                throw $e;
            }
        }
    }


    public function is_available($not, info $info, $grabthelot, $userid) {
        // check if local_adler is available
        $plugins = $this->core_plugin_manager_instance->get_installed_plugins('local');
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

    private function get_room_name($roomid) {
        // TODO local_adler
        global $DB;
        $room = $DB->get_record('course_sections', ['id' => $roomid]);
        return $room->name;
    }

    private function replace_room_ids_with_names($statement) {
        $chars = ['(', ')', '^', 'v', '!'];
        $splitted_statement = [$statement];
        foreach ($chars as $char) {
            $new_statement = [];
            foreach ($splitted_statement as $part) {
                $part = explode($char, $part);
                for ($i = 0; $i < count($part); $i++) {
                    $new_statement[] = $part[$i];
                    if ($i < count($part) - 1) {
                        $new_statement[] = $char;
                    }
                }
            }
            $splitted_statement = $new_statement;
        }

        $updated_statement = "";
        foreach ($splitted_statement as $part) {
            if (is_numeric($part)) {
                if ($this->evaluate_room($part, $GLOBALS['USER']->id)) {
                    $updated_statement .= '<span style="color: green;">';
                } else {
                    $updated_statement .= '<span style="color: red;">';
                }
                $updated_statement .= htmlspecialchars($this->get_room_name($part), ENT_QUOTES, 'UTF-8') . '</span> ';
            } else {
                switch ($part) {
                    case '^':
                        $updated_statement .= get_string("condition_operator_pretty_and", "availability_adler"). ' ';
                        break;
                    case 'v':
                        $updated_statement .= get_string("condition_operator_pretty_or", "availability_adler") . ' ';
                        break;
                    case '!':
                        $updated_statement .= get_string("condition_operator_pretty_not", "availability_adler") . ' ';
                        break;
                    default:
                        $updated_statement .= $part;
                }
            }
        }

        return "\"" . trim($updated_statement) . "\"";
    }

    public function get_description($full, $not, info $info) {
        $translation_key = $not ? 'description_previous_rooms_required_not' : 'description_previous_rooms_required';
        return get_string($translation_key, 'availability_adler', $this->replace_room_ids_with_names($this->condition));
    }

    protected function get_debug_string() {
        return 'Room condition: ' . $this->condition;
    }

    public function save() {
        return (object)[
            'type' => 'adler',
            'condition' => $this->condition,
        ];
    }

    /** Restore logic for completion condition
     * Translates the room/section ids in the condition from the backup file to the new ids in the restored course.
     *
     * @param string $restoreid
     * @param int $courseid
     * @param base_logger $logger
     * @param string $name
     * @return bool
     * @throws moodle_exception
     */
    public function update_after_restore($restoreid, $courseid, base_logger $logger, $name) {
        $updated_condition = "";
        for ($i = 0; $i < strlen($this->condition); $i++) {
            $char = $this->condition[$i];
            if (is_numeric($char)) {
                // find last digit of number
                $j = $i + 1;
                while ($j < strlen($this->condition) && is_numeric($this->condition[$j])) {
                    $j++;
                }
                $number = substr($this->condition, $i, $j - $i);
                $i = $j - 1;

                // add updated id to new string
                $updated_id = $this->callStatic(restore_dbops::class, 'get_backup_ids_record', $restoreid, 'course_section', $number);
                if ($updated_id == false) {
                    throw new moodle_exception('unknown_section', 'availability_adler', '', NULL, 'section: ' . $number);
                }
                $updated_condition .= $updated_id->newitemid;
            } else {
                $updated_condition .= $char;
            }
        }

        $condition_changed = $updated_condition != $this->condition;
        $this->condition = $updated_condition;
        return $condition_changed;
    }
}
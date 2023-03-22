<?php

namespace availability_adler;


use availability_adler\lib\availability_adler_testcase;
use base_logger;
use core_availability\info;
use core_plugin_manager;
use moodle_exception;
use ReflectionClass;
use ReflectionMethod;
use restore_dbops;

global $CFG;
require_once($CFG->dirroot . '/availability/condition/adler/tests/lib/adler_testcase.php');


class condition_test extends availability_adler_testcase {
    public function provide_test_construct_data() {
        return [
            'valid' => [
                'structure' => (object) [
                    'type' => 'adler',
                    'condition' => '(1)^(2)'
                ],
                'expected_exception' => null,
                'expected_condition' => '(1)^(2)'
            ],
            'invalid condition' => [
                'structure' => (object) [
                    'type' => 'adler',
                    'condition' => '(1)^(2'
                ],
                'expected_exception' => 'invalid_parameter_exception',
                'expected_condition' => null
            ],
            'missing condition' => [
                'structure' => (object) [
                    'type' => 'adler',
                ],
                'expected_exception' => 'coding_exception',
                'expected_condition' => null
            ],
        ];
    }

    /**
     * @dataProvider provide_test_construct_data
     */
    public function test_construct($structure, $expected_exception, $expected_condition) {
        if ($expected_exception) {
            $this->expectException($expected_exception);
        }

        $condition = new condition($structure);

        // get condition
        $condition_reflection = new ReflectionClass($condition);
        $condition_property = $condition_reflection->getProperty('condition');
        $condition_property->setAccessible(true);
        $condition = $condition_property->getValue($condition);

        $this->assertEquals($expected_condition, $condition);

    }

    public function provide_test_evaluate_room_requirements_data() {
        return [
            '1' => [
                'statement' => "(5)v((7)^(4))",
                'expected' => true,
                'exception' => null,
                'room_states' => [
                    5 => true,
                    7 => true,
                    4 => false
                ]
            ],
            '2' => [
                'statement' => "(5)v((7)^(4))",
                'expected' => false,
                'exception' => null,
                'room_states' => [
                    5 => false,
                    7 => true,
                    4 => false
                ]
            ],
            '3' => [
                'statement' => "(1)",
                'expected' => false,
                'exception' => null,
                'room_states' => [
                    1 => false,
                ]
            ],
            '4' => [
                'statement' => "(1)",
                'expected' => true,
                'exception' => null,
                'room_states' => [
                    1 => true,
                ]
            ],
            '5' => [
                'statement' => "(1)^(2)",
                'expected' => true,
                'exception' => null,
                'room_states' => [
                    1 => true,
                    2 => true,
                ]
            ],
            '6' => [
                'statement' => "(1)^(2)",
                'expected' => false,
                'exception' => null,
                'room_states' => [
                    1 => true,
                    2 => false,
                ]
            ],
            '7' => [
                'statement' => "(1)v(2)",
                'expected' => true,
                'exception' => null,
                'room_states' => [
                    1 => false,
                    2 => true,
                ]
            ],
            '8' => [
                'statement' => "1v(2)",
                'expected' => true,
                'exception' => null,
                'room_states' => [
                    1 => true,
                    2 => false,
                ]
            ],
            '9' => [
                'statement' => "((1)^(2))v((3)^(4))",
                'expected' => true,
                'exception' => null,
                'room_states' => [
                    1 => true,
                    2 => true,
                    3 => true,
                    4 => true,
                ]
            ],
            '10' => [
                'statement' => "!((1)^(2))",
                'expected' => false,
                'exception' => null,
                'room_states' => [
                    1 => true,
                    2 => true,
                ]
            ],
            '11' => [
                'statement' => "(w)^(2)",
                'expected' => true,
                'exception' => 'invalid_parameter_exception',
                'room_states' => [
                    1 => true,
                    2 => false,
                ]
            ],
        ];
    }


    /**
     * @dataProvider provide_test_evaluate_room_requirements_data
     */
    public function test_evaluate_room_requirements($statement, $expected, $exception, $room_states) {
        // map $room_states to the format of $room_states_map_format
        $room_states = array_map(function ($key, $value) {
            return [$key, 0, $value];
        }, array_keys($room_states), $room_states);

        // create mock for condition evaluate_room
        $mock = $this->getMockBuilder(condition::class)
            ->disableOriginalConstructor()
            ->setMethods(['evaluate_room'])
            ->getMock();
        // set return values for evaluate_room
        $mock->method('evaluate_room')
            ->will($this->returnValueMap($room_states));

        // make evaluate_room_requirements accessible
        $method = new ReflectionMethod(condition::class, 'evaluate_room_requirements');
        $method->setAccessible(true);

        // if $exception is not null, expect an exception
        if ($exception !== null) {
            $this->expectException($exception);
        }

        $this->assertEquals($expected, $method->invoke($mock, $statement, 0));
    }

    public function test_get_description() {
        $info_mock = $this->getMockBuilder(info::class)
            ->disableOriginalConstructor()
            ->getMock();
        $condition = new condition((object)['type' => 'adler', 'condition' => '1']);
        $result = $condition->get_description(true, 'test', $info_mock);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_get_debug_string() {
        $condition = new condition((object)['type' => 'adler', 'condition' => '1']);

        // make get_debug_string accessible
        $method = new ReflectionMethod(condition::class, 'get_debug_string');
        $method->setAccessible(true);

        // call get_debug_string
        $result = $method->invoke($condition);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_save() {
        $adler_statement = (object)['type' => 'adler', 'condition' => '1'];

        $condition = new condition($adler_statement);
        $result = $condition->save();

        $this->assertEquals($adler_statement, $result);
    }

    public function provide_test_is_available_data() {
        return [
            '1' => [
                'installed_plugins' => ['adler' => '123'],
                'evaluate_room_requirements' => true,
                'not' => false,
                'expected' => true
            ],
            '2' => [
                'installed_plugins' => ['adler' => '123'],
                'evaluate_room_requirements' => true,
                'not' => true,
                'expected' => false
            ],
            '3' => [
                'installed_plugins' => ['adler' => '123'],
                'evaluate_room_requirements' => false,
                'not' => false,
                'expected' => false
            ],
            '4' => [
                'installed_plugins' => [],
                'evaluate_room_requirements' => true,
                'not' => true,
                'expected' => false
            ],
        ];
    }

    /**
     * @dataProvider provide_test_is_available_data
     */
    public function test_is_available(array $installed_plugins, bool $evaluate_room_requirements, bool $not, bool $expected) {
        $info_mock = $this->getMockBuilder(info::class)
            ->disableOriginalConstructor()
            ->getMock();


        $core_plugin_manager_mock = $this->getMockBuilder(core_plugin_manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $core_plugin_manager_mock->method('get_installed_plugins')
            ->willReturn($installed_plugins);


        $condition_mock = $this->getMockBuilder(condition::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['evaluate_room_requirements'])
            ->getMock();
        $condition_mock->method('evaluate_room_requirements')
            ->willReturn($evaluate_room_requirements);
        // set protected property core_plugin_manager_instance of condition_mock
        $reflection = new ReflectionClass($condition_mock);
        $property = $reflection->getProperty('core_plugin_manager_instance');
        $property->setAccessible(true);
        $property->setValue($condition_mock, $core_plugin_manager_mock);
        // set condition
        $property = $reflection->getProperty('condition');
        $property->setAccessible(true);
        $property->setValue($condition_mock, '1');


        // invoke method is_available on $reflection
        $result = $condition_mock->is_available($not, $info_mock, true, 0);
        // alternative approach
//        $method = $reflection->getMethod('is_available');
//        $result = $method->invoke($condition_mock, $not, $info_mock, true, 0);


        $this->assertEquals($expected, $result);
    }

    public function provide_test_update_after_restore_data() {
        return [
            '1' => [
                'condition' => "(1)^(20)",
                'backup_id_mappings' => [
                    [1, (object)["newitemid" => "3"]],
                    [20, (object)["newitemid" => "4"]],
                ],
                'expected_updated_condition' => "(3)^(4)",
                'expect_exception' => false,
            ],
            '2' => [
                'condition' => "(1)^(2)",
                'backup_id_mappings' => [
                    [1, (object)["newitemid" => "3"]],
                    [2, false],
                ],
                'expected_updated_condition' => null,
                'expect_exception' => moodle_exception::class,
            ],
        ];
    }

    /**
     * @dataProvider provide_test_update_after_restore_data
     */
    public function test_update_after_restore($condition, $backup_id_mappings, $expected_updated_condition, $expect_exception) {
        global $CFG;
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        // unused but required variables
        $restoreid = 123;
        $courseid = 456;
        $base_logger_mock = $this->getMockBuilder(base_logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $name = 'test';

        // create get_backup_ids_record return map
        $return_map = [];
        foreach ($backup_id_mappings as $mapping) {
            $return_map[] = [restore_dbops::class, 'get_backup_ids_record', $restoreid, 'course_section', (string)$mapping[0], $mapping[1]];
        }

        // mock condition
        $condition_mock = $this->getMockBuilder(condition::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['callStatic'])
            ->getMock();
        $condition_mock->method('callStatic')
            ->will($this->returnValueMap($return_map));

        // set condition
        $reflection = new ReflectionClass($condition_mock);
        $property = $reflection->getProperty('condition');
        $property->setAccessible(true);
        $property->setValue($condition_mock, $condition);

        // setup exception
        if ($expect_exception) {
            $this->expectException($expect_exception);
        }

        // call update_after_restore
        $result = $condition_mock->update_after_restore($restoreid, $courseid, $base_logger_mock, $name);

        // verify result
        $this->assertEquals(true, $result);
        $updated_condition = $property->getValue($condition_mock);
        $this->assertEquals($expected_updated_condition, $updated_condition);
    }
}

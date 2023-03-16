<?php

namespace availability_adler;


use availability_adler\lib\availability_adler_testcase;
use core_availability\info;
use core_plugin_manager;
use ReflectionClass;
use ReflectionMethod;

global $CFG;
require_once($CFG->dirroot . '/availability/condition/adler/tests/lib/adler_testcase.php');


class condition_test extends availability_adler_testcase {
    public function setUp(): void {
        parent::setUp();
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
//        $method = $reflection->getMethod('is_available');
//        $result = $method->invoke($condition_mock, $not, $info_mock, true, 0);
        $result = $condition_mock->is_available($not,$info_mock,true,0);

        $this->assertEquals($expected, $result);
    }
}

<?php

namespace availability_adler;


use availability_adler\lib\availability_adler_testcase;
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
                'room_states' => [
                    5 => true,
                    7 => true,
                    4 => false
                ]
            ],
            '2' => [
                'statement' => "(5)v((7)^(4))",
                'expected' => false,
                'room_states' => [
                    5 => false,
                    7 => true,
                    4 => false
                ]
            ],
            '3' => [
                'statement' => "(1)",
                'expected' => false,
                'room_states' => [
                    1 => false,
                ]
            ],
            '4' => [
                'statement' => "(1)",
                'expected' => true,
                'room_states' => [
                    1 => true,
                ]
            ],
            '5' => [
                'statement' => "(1)^(2)",
                'expected' => true,
                'room_states' => [
                    1 => true,
                    2 => true,
                ]
            ],
            '6' => [
                'statement' => "(1)^(2)",
                'expected' => false,
                'room_states' => [
                    1 => true,
                    2 => false,
                ]
            ],
            '7' => [
                'statement' => "(1)v(2)",
                'expected' => true,
                'room_states' => [
                    1 => false,
                    2 => true,
                ]
            ],
            '8' => [
                'statement' => "1v(2)",
                'expected' => true,
                'room_states' => [
                    1 => true,
                    2 => false,
                ]
            ],
            '9' => [
                'statement' => "((1)^(2))v((3)^(4))",
                'expected' => true,
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
                'room_states' => [
                    1 => true,
                    2 => true,
                ]
            ],
        ];
    }


    /**
     * @dataProvider provide_test_evaluate_room_requirements_data
     */
    public function test_evaluate_room_requirements($statement, $expected, $room_states) {
        // map $room_states to the format of $room_states_map_format
        $room_states = array_map(function($key, $value) {
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
//        $method->setAccessible(true);
//
//
        $this->assertEquals($expected, $method->invoke($mock, $statement, 0));
//        $this->assertEquals($expected, $mock->evaluate_room_requirements($statement, 0));


    }
}

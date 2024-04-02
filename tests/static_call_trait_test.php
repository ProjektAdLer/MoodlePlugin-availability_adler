<?php


use availability_adler\lib\adler_testcase;
use availability_adler\static_call_trait;

global $CFG;
require_once($CFG->dirroot . '/availability/condition/adler/tests/lib/adler_testcase.php');


class static_call_trait_test extends adler_testcase {
    public function test_call_static() {
        $testclass = new testclass_1_static_call_trait_test();
        $result = $testclass->test_method('test', 'test2', 'test3');

        $this->assertEquals(['test', 'test2', 'test3'], testclass_2_static_call_trait_test::$call_params);
        $this->assertEquals('test', $result);
    }
}


class testclass_1_static_call_trait_test {
    use static_call_trait;

    public function test_method(...$params) {
        return $this->callStatic(testclass_2_static_call_trait_test::class, 'test_method', ...$params);
    }
}

class testclass_2_static_call_trait_test {
    public static array $call_params = [];

    public static function test_method(...$params) {
        self::$call_params = $params;
        return $params[0];
    }
}
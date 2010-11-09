<?php

require_once(COMB_APPLICATION_ROOT . 'Comb' . DIRECTORY_SEPARATOR . 'Registry.php');

/**
 * Test class for Comb_Registry.
 * Generated by PHPUnit on 2010-11-09 at 21:12:57.
 */
class Comb_RegistryTest extends PHPUnit_Framework_TestCase
{

    public function testSet()
    {
        Comb_Registry::set('test', 'abc');
        $this->assertEquals('abc', Comb_Registry::get('test'), 'setting and getting string');

        $obj = clone $this;
        $class = get_class($obj);
        Comb_Registry::set('test', $obj);
        $this->assertEquals($class, get_class(Comb_Registry::get('test')), 'setting and getting object');

        $obj = new stdClass();
        $obj->test = '123';
        Comb_Registry::set('test', $obj);
        $obj->test = '321';
        $this->assertEquals('321', Comb_Registry::get('test')->test, 'Preserving object reference');
    }
}
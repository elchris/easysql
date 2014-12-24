<?php
/**
 * Created with PhpStorm.
 * User: Chris Holland
 * Date: 11/27/14
 * Time: 9:20 PM
 */

namespace com\github\elchris\easysql\tests;


abstract class EasySQLUnitTest extends \PHPUnit_Framework_TestCase
{
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }//invokeMethod

    public function odebug($o)
    {
        $this->debug(print_r($o, true));
    }//debug

    public function debug($msg)
    {
        print('*** ' . $msg . "\n");
    }//odebug
}//EasySQLUnitTest
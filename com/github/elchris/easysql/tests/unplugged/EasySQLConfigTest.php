<?php
/**
 * Created with PhpStorm.
 * User: Chris Holland
 * Date: 12/7/14
 * Time: 2:23 PM
 */

namespace com\github\elchris\easysql\tests;

use com\github\elchris\easysql\EasySQLConfig;

class EasySQLConfigTest extends EasySQLUnitTest
{

    const TEST_APP_CONFIG_JSON = '{"app":{"master":{"string":"mysql:host=m;dbname=d","u":"u","p":"p","connection":null},"slave":{"string":"mysql:host=sm;dbname=sd","u":"su","p":"sp","connection":null}}}';

    public function testSetProps()
    {
        $c = new EasySQLConfig();
        $c
            ->addApplication('app')
            ->setMaster('m', 'd', 'u', 'p')
            ->setSlave('sm', 'sd', 'su', 'sp');;
        $a1 = $c->getApp('app');
        $this->assertEquals('mysql:host=m;dbname=d', $a1->getMasterConnectionString());
        $this->assertEquals('u', $a1->getMasterUsername());
        $this->assertEquals('p', $a1->getMasterPassword());

        $this->assertEquals('mysql:host=sm;dbname=sd', $a1->getSlaveConnectionString());
        $this->assertEquals('su', $a1->getSlaveUsername());
        $this->assertEquals('sp', $a1->getSlavePassword());

        $this->assertEquals(self::TEST_APP_CONFIG_JSON, $c->getAsJson());
    }//testSetProps

    /**
     * @expectedException \Exception
     */
    public function testSetSlaveNoMasterThrowsException()
    {
        $c = new EasySQLConfig();
        $c
            ->addApplication('app')
            ->setSlave('sm', 'sd', 'su', 'sp');;
    }//testSetSlaveNoMasterThrowsException

}//EasySQLConfigTest
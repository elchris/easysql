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

    const TEST_APP_CONFIG_JSON = '{"app":{"driverOptions":[],"master":{"string":"mysql:host=m;dbname=d","u":"u","p":"p","connection":null},"slave":{"string":"mysql:host=sm;dbname=sd","u":"su","p":"sp","connection":null}}}';
    const SQL_SERVER_CONFIG_JSON = '{"sqlserverapp":{"driverOptions":[],"master":{"string":"sqlsrv:Server=sqlserverhost-master;Database=database","u":"username","p":"password","connection":null},"slave":{"string":"sqlsrv:Server=sqlserverhost-slave;Database=database","u":"username","p":"password","connection":null}}}';

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

    public function testSqlServer()
    {
        $c = new EasySQLConfig();
        $c->addApplication('sqlserverapp',EasySQLConfig::DRIVER_SQLSRV)
            ->setMaster('sqlserverhost-master','database','username','password')
            ->setSlave('sqlserverhost-slave','database','username','password')
        ;
        $this->assertEquals(self::SQL_SERVER_CONFIG_JSON, $c->getAsJson());
    }//testSqlServer

    public function testSetDriverOptions()
    {
        $c = new EasySQLConfig();
        $c->addApplication('sqlserverapp',EasySQLConfig::DRIVER_SQLSRV)
            ->setMaster('sqlserverhost-master','database','username','password')
            ->setSlave('sqlserverhost-slave','database','username','password')
            ->setDriverOptions(array('foo' => 'bar'))
        ;
        $arrayConfig = $c->getAsArray();
        $options = $arrayConfig['sqlserverapp']['driverOptions'];
        $this->assertArrayHasKey('foo',$options);
        $this->assertEquals('bar',$options['foo']);
    }//testSetDriverOptions
}//EasySQLConfigTest
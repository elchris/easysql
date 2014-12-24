<?php
/**
 * Created with PhpStorm.
 * User: Chris Holland
 * Date: 12/1/14
 * Time: 10:51 PM
 */

namespace com\github\elchris\easysql\tests;


use com\github\elchris\easysql\EasySQLConfig;
use com\github\elchris\easysql\EasySQLConnectionManager;
use com\github\elchris\easysql\EasySQLContext;
use com\github\elchris\easysql\EasySQLDB;

class CtxTestConfig
{
    public static function getUnitTestConfig()
    {
        $fig = new EasySQLConfig();
        $fig
            ->addApplication('application1')
            ->setMaster('masterconnection1.host', 'name1', 'uname1master', 'pass1master')
            ->setSlave('slaveconnection1.host', 'name1', 'uname1slave', 'pass1slave');
        $fig
            ->addApplication('application2')
            ->setMaster('masterconnection2.host', 'name2', 'uname2master', 'pass2master')
            ->setSlave('slaveconnection2.host', 'name2', 'uname2slave', 'pass2slave');
        return $fig;
    }//getUnitTestConfig
}//CtxTestConfig

class EasySQLContextUnitTest extends EasySQLUnitTest
{
    public function testContext()
    {
        $context = new EasySQLContext();
        $m = new EasySQLConnectionManager($context);
        $m->setNewConfig(CtxTestConfig::getUnitTestConfig());
        $m->getCurrentConfig()->getAsArray();
        foreach($m->getCurrentConfig()->getAsArray() as $appName => $appConfig) {
            $context->setConnectionConfig($appName, $appConfig);
            $context->setConnection($appName, 'master', new EasySQLDB($appConfig['master']['string'],$appConfig['master']['u'], $appConfig['master']['p'], true));
            $context->setConnection($appName, 'slave', new EasySQLDB($appConfig['slave']['string'],$appConfig['slave']['u'], $appConfig['slave']['p'], true));
            $this->assertInstanceOf('com\github\elchris\easysql\IEasySQLDB', $context->getConnections()[$appName]['master']['connection']);
            $this->assertInstanceOf('com\github\elchris\easysql\IEasySQLDB', $context->getConnections()[$appName]['slave']['connection']);
            $this->assertInstanceOf('com\github\elchris\easysql\EasySQLDB', $context->getConnections()[$appName]['master']['connection']);
            $this->assertInstanceOf('com\github\elchris\easysql\EasySQLDB', $context->getConnections()[$appName]['slave']['connection']);
        }
    }//testContext
}//EasySQLUnitTest
 
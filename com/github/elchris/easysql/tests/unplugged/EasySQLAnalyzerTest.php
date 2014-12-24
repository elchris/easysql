<?php
/**
 * Created with PhpStorm.
 * User: Chris Holland
 * Date: 12/19/14
 * Time: 7:29 PM
 */

namespace com\github\elchris\easysql\tests\unplugged;


use com\github\elchris\easysql\EasySQLQueryAnalyzer;
use com\github\elchris\easysql\tests\EasySQLUnitTest;

class EasySQLAnalyzerTest extends EasySQLUnitTest
{
    public function testIsSlave()
    {
        $a = new EasySQLQueryAnalyzer();
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_SLAVE, $a->getDbType('SELECT * FROM bleh;'));
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_SLAVE, $a->getDbType('SELECT * FROM bleh;'));
    }//testIsSlave

    public function testIsMaster()
    {
        $a = new EasySQLQueryAnalyzer();
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_MASTER, $a->getDbType('call sproc;'));
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_MASTER, $a->getDbType('CALL sproc;'));
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_MASTER,
            $a->getDbType('UPDATE foo SET bleh=1 WHERE foo=bar;'));
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_MASTER,
            $a->getDbType('UPDATE foo SET bleh=1 WHERE foo=bar;'));
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_MASTER, $a->getDbType('INSERT INTO foo (a) SELECT a=1;'));
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_MASTER, $a->getDbType('INSERT INTO foo (a) SELECT a=1;'));
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_MASTER, $a->getDbType('DELETE FROM foo'));
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_MASTER, $a->getDbType('DELETE FROM foo'));
    }//testIsMaster
}//EasySQLAnalyzerTest
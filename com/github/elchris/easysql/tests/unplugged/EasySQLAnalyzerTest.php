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
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_SLAVE,$a->getDbType('select * from bleh;'));
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_SLAVE,$a->getDbType('SELECT * from bleh;'));
    }//testIsSlave

    public function testIsMaster()
    {
        $a = new EasySQLQueryAnalyzer();
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_MASTER,$a->getDbType('call sproc;'));
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_MASTER,$a->getDbType('CALL sproc;'));
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_MASTER,$a->getDbType('update foo set bleh=1 where foo=bar;'));
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_MASTER,$a->getDbType('UPDATE foo set bleh=1 where foo=bar;'));
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_MASTER,$a->getDbType('insert into foo (a) select a=1;'));
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_MASTER,$a->getDbType('INSERT into foo (a) select a=1;'));
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_MASTER,$a->getDbType('delete from foo'));
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_MASTER,$a->getDbType('DELETE from foo'));
    }//testIsMaster
}//EasySQLAnalyzerTest
<?php
/**
 * Created with PhpStorm.
 * User: chris
 * Date: 11/22/13
 * Time: 9:44 AM
 */

namespace com\github\elchris\easysql\tests;

use com\github\elchris\easysql\EasySQLDB;

/**
 * Class EasySQLDBUnitTest
 * @package com\github\elchris\easysql\tests
 */
class EasySQLDBUnitTest extends EasySQLUnitTest
{
    public function testGetConnectionString()
    {
        $c = new EasySQLDB('mysql:test', 'username', 'thepassword', true);
        $this->assertEquals('mysql:test', $c->getConnectionString());
    }//testGetConnectionString

    public function testGetUserName()
    {
        $c = new EasySQLDB('mysql:test', 'username', 'thepassword', true);
        $this->assertEquals('username', $c->getUsername());
    }//testGetUserName

    public function testGetId()
    {
        $c = new EasySQLDB('mysql:test', 'username', 'thepassword', true);
        $this->assertNotNull($c->getId());
        $this->assertNotEmpty($c->getId());
    }//testGetId

    public function testPrepareQuery()
    {
        $c = new EasySQLDB('mysql:test', 'username', 'thepassword', true);
        $prepared = $c->prepareQuery('SELECT * FROM sometable');
        $this->assertNotNull($prepared);
        $this->assertInstanceOf('com\github\elchris\easysql\MockEasySQLDBStatement', $prepared);
        $this->assertInstanceOf('com\github\elchris\easysql\IEasySQLDBStatement', $prepared);
    }//testPrepareQuery

    public function testReleaseResources()
    {
        $c = new EasySQLDB('mysql:test', 'username', 'thepassword', true);
        $prepared1 = $c->prepareQuery('SELECT * FROM sometable');
        $prepared2 = $c->prepareQuery('SELECT * FROM sometable1');
        $prepared3 = $c->prepareQuery('SELECT * FROM sometable2');
        $prepared4 = $c->prepareQuery('SELECT * FROM sometable3');
        $prepared5 = $c->prepareQuery('SELECT * FROM sometable');
        $prepared6 = $c->prepareQuery('SELECT * FROM sometable1');
        $prepared7 = $c->prepareQuery('SELECT * FROM sometable2');
        $prepared8 = $c->prepareQuery('SELECT * FROM sometable3');
        $this->assertNotEquals($prepared1->getId(), $prepared2->getId());
        $this->assertNotEquals($prepared2->getId(), $prepared3->getId());
        $this->assertNotEquals($prepared3->getId(), $prepared4->getId());
        $this->assertEquals($prepared1->getId(), $prepared5->getId());
        $this->assertEquals($prepared2->getId(), $prepared6->getId());
        $this->assertEquals($prepared3->getId(), $prepared7->getId());
        $this->assertEquals($prepared4->getId(), $prepared8->getId());
        $releasedStatements = $c->releaseResources();
        $this->assertEquals(4, count($releasedStatements));
    }//testReleaseResources

    public function testReleaseResourcesWithBusyStatements()
    {
        $c = new EasySQLDB('mysql:test', 'username', 'thepassword', true);
        $prepared1 = $c->prepareQuery('SELECT * FROM sometable');
        $prepared1->execute();
        $prepared1->fetchAsCollection();
        $prepared2 = $c->prepareQuery('SELECT * FROM sometable1');
        $prepared2->execute();
        // simulating a statement not fully-used on $prepared2
        // we are not calling $prepared2->fetchAsCollection();
        $prepared3 = $c->prepareQuery('SELECT * FROM sometable2');
        $prepared3->execute();
        $prepared3->fetchAsCollection();
        $prepared4 = $c->prepareQuery('SELECT * FROM sometable3');
        $prepared4->execute();
        $prepared4->fetchAsCollection();
        $prepared5 = $c->prepareQuery('SELECT * FROM sometable');
        $prepared5->execute();
        $prepared5->fetchAsCollection();
        $prepared6 = $c->prepareQuery('SELECT * FROM sometable1');//returning a replaced statement
        $prepared6->execute();
        $prepared6->fetchAsCollection();
        $prepared7 = $c->prepareQuery('SELECT * FROM sometable2');
        $prepared7->execute();
        $prepared7->fetchAsCollection();
        $prepared8 = $c->prepareQuery('SELECT * FROM sometable3');
        $prepared8->execute();
        $prepared8->fetchAsCollection();
        $this->assertNotEquals($prepared1->getId(), $prepared2->getId());
        $this->assertNotEquals($prepared2->getId(), $prepared3->getId());
        $this->assertNotEquals($prepared3->getId(), $prepared4->getId());
        $this->assertEquals($prepared1->getId(), $prepared5->getId());
        /*
         * $prepared2 above did not invoke fetchAll so when
         * $prepared6 comes-in with the same query, instead of reusing $prepared2's statement,
         * a new one gets generated because $prepared2's statement is still marked as "busy".
         */
        $this->assertNotEquals($prepared2->getId(), $prepared6->getId()); //verifying new statement was generated here
        $this->assertEquals($prepared3->getId(), $prepared7->getId());
        $this->assertEquals($prepared4->getId(), $prepared8->getId());
        $releasedStatements = $c->releaseResources();
        $this->assertEquals(4, count($releasedStatements));
    }//testReleaseResourcesWithBusyStatements

    public function testStatementStash()
    {
        $c = new EasySQLDB('mysql:test', 'username', 'thepassword', true);
        $c->prepareQuery('SELECT * FROM sometable');
        $c->prepareQuery('SELECT * FROM sometable2');
        $c->prepareQuery('SELECT * FROM sometable3');
        $c->prepareQuery('SELECT * FROM sometable');
        $statements = $c->getStatementStash();
        $this->assertCount(3, $statements);
        foreach ($statements as $statement) {
            $this->assertInstanceOf('com\github\elchris\easysql\IEasySQLDBStatement', $statement);
        }
    }//testStatementStash
}//EasySQLDBUnitTest
 
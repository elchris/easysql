<?php
/**
 * Created with PhpStorm.
 * User: chris
 * Date: 11/23/13
 * Time: 1:51 PM
 */

namespace com\github\elchris\easysql\tests;

use com\github\elchris\easysql\EasySQLBean;
use com\github\elchris\easysql\MockEasySQLDBStatement;

class TestBean extends EasySQLBean
{

    /**
     * @return string
     */
    public function getClassName()
    {
        return get_class($this);
    }//getClassName
}//TestBean

class EasySQLDBStatementUnitTest extends EasySQLUnitTest
{

    public function testBindValueByIndex()
    {
        $s = new MockEasySQLDBStatement();
        $s->bindValueByIndex(1, 'one');
        $s->bindValueByIndex(2, 'two');
        $binds = $s->getIndexValueBinds();
        $this->assertEquals('one', $binds[1]);
        $this->assertEquals('two', $binds[2]);
    }//testBindValueByIndex

    public function testBindValueByName()
    {
        $s = new MockEasySQLDBStatement();
        $s->bindValueByName('one', 'valueOne');
        $s->bindValueByName('two', 'valueTwo');
        $binds = $s->getNameValueBinds();
        $this->assertEquals('valueOne', $binds['one']);
        $this->assertEquals('valueTwo', $binds['two']);
    }//testBindValueByName

    public function testFetchAsCollection()
    {
        $s = new MockEasySQLDBStatement();
        $results = $s->fetchAsCollection();
        $this->assertEquals(3, count($results));
    }//testFetchAsCollection

    public function testFetchAsCollectionOf()
    {
        $s = new MockEasySQLDBStatement();
        $results = $s->fetchAsCollectionOf(new TestBean());
        $this->assertEquals(3, count($results));
        $this->assertInstanceOf('com\github\elchris\easysql\IEasySQLBean', $results[0]);
        $this->assertInstanceOf('com\github\elchris\easysql\tests\TestBean', $results[0]);
    }//testFetchAsCollectionOf
}//EasySQLDBStatementUnitTest
 
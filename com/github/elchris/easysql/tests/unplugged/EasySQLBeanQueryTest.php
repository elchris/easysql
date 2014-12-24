<?php
/**
 * Created with PhpStorm.
 * User: Chris Holland
 * Date: 12/19/14
 * Time: 3:52 PM
 */

namespace com\github\elchris\easysql\tests\unplugged;


use com\github\elchris\easysql\EasySQLBean;
use com\github\elchris\easysql\EasySQLBeanQuery;
use com\github\elchris\easysql\tests\EasySQLUnitTest;

class TestBeanOne extends EasySQLBean
{
    public $propOne = 'propOneValue';
    public $propTwo = 'propTwoValue';
}//TestBeanOne

class EasySQLBeanQueryTest extends EasySQLUnitTest
{
    public function testGetInsertQueryAndPropsForBeanArrayAndTable()
    {
        $beans = array(new TestBeanOne(), new TestBeanOne(), new TestBeanOne());
        $b = new EasySQLBeanQuery();
        $results = $b->getInsertQueryAndPropsForBeanArrayAndTable($beans);
        $values = $results[0];
        $query = $results[1];
        $this->assertEquals('insert into testbeanone (propOne,propTwo) values (?,?),(?,?),(?,?);',$query);
        $this->assertEquals('["propOneValue","propTwoValue","propOneValue","propTwoValue","propOneValue","propTwoValue"]', json_encode($values));
    }//testGetInsertQueryAndPropsForBeanArrayAndTable

    public function testSomething()
    {
        $bean = new TestBeanOne();
        $b = new EasySQLBeanQuery();
        $results = $b->getInsertQueryAndPropsForBeanTable($bean);
        $values = $results[0];
        $query = $results[1];
        $this->assertEquals('insert into testbeanone (propOne, propTwo) values (:propOne, :propTwo);', $query);
        $this->assertEquals('{"propOne":"propOneValue","propTwo":"propTwoValue"}',json_encode($values));
    }
}//EasySQLBeanQueryTest
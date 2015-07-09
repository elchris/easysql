<?php
/**
 * Created with PhpStorm.
 * User: chris
 * Date: 11/21/13
 * Time: 9:43 AM
 */

namespace com\github\elchris\easysql\tests;

use com\github\elchris\easysql\EasySQL;
use com\github\elchris\easysql\EasySQLBean;
use com\github\elchris\easysql\EasySQLConfig;
use com\github\elchris\easysql\EasySQLContext;
use com\github\elchris\easysql\EasySQLQueryAnalyzer;
use com\github\elchris\easysql\IEasySQLBean;
use Exception;

class TestConfig
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
}//TestConfig

class MyBean extends EasySQLBean
{
    public $propOne;
    public $propTwo;
}//MyBean

class RealDAO extends EasySQL
{
    public function __construct(EasySQLContext $ctx)
    {
        parent::__construct($ctx, 'application1', TestConfig::getUnitTestConfig());
    }

    public function getStuff()
    {
        return $this->getAsCollectionOf(new MyBean(), 'SELECT * FROM atable;');
    }//getStuff
}//RealDAO

class TestDAO extends EasySQL
{
    const DEFAULT_APPLICATION_NAME = 'application1';

    private $query = null;
    private $name = self::DEFAULT_APPLICATION_NAME;

    public function __construct(EasySQLContext $ctx, $testQuery, $applicationName = self::DEFAULT_APPLICATION_NAME)
    {
        $this->query = $testQuery;
        $this->name = $applicationName;
        parent::__construct($ctx, $applicationName, TestConfig::getUnitTestConfig());
    }

    public function isTest()
    {
        return $this->isTestMode();
    }//isTestMode

    public function isTestMode()
    {
        return true;
    }//isTest()

    /**
     * @return string
     */
    public function getApplicationName()
    {
        return $this->name;
    }//getApplicationName

    public function testDbType()
    {
        return $this->qAnalyzer->getDbType($this->query);
    }//testDbType

    public function testIsRead()
    {
        return $this->qAnalyzer->isReadQuery($this->query);
    }//testIsRead

    public function getTestConnectionForQuery()
    {
        return $this->getQueryConnection($this->query);
    }//getTestConnectionForQuery

    /**
     * @param array $params
     * @return IEasySQLBean
     */
    public function getMyBeanCollection($params)
    {
        return $this->getAsCollectionOf(new MyBean(), $this->query, $params);
    }//queryStuff

    public function getMyBeanInsertWithTableName()
    {
        return $this->getMyBeanInsert('mybean');
    }//getMyBeanInsert

    public function getMyBeanInsert($tableName = null)
    {
        $b = new MyBean();
        $b->propOne = 'somethingOne';
        $b->propTwo = 'somethingTwo';
        $this->insertSingleBean($b);
        return $this->beanQuery->getInsertQueryAndPropsForBeanTable($b, $tableName);
    }//getMyBeanInsertWithTableName

    public function getInsertCollectionOfBeanWithTable()
    {
        return $this->getInsertCollectionOfBean('mybean');
    }//getInsertCollectionOfBeans

    public function getInsertCollectionOfBean($tableName = null)
    {
        $b1 = new MyBean();
        $b1->propOne = 'somethingOne Row 1';
        $b1->propTwo = 'somethingTwo Row 1';
        $b2 = new MyBean();
        $b2->propOne = 'somethingOne Row 2';
        $b2->propTwo = 'somethingTwo Row 2';
        $b3 = new MyBean();
        $b3->propOne = 'somethingOne Row 3';
        $b3->propTwo = 'somethingTwo Row 3';
        $this->insertCollectionOfBeans(array($b1, $b2, $b3), $tableName);
        return $this->beanQuery->getInsertQueryAndPropsForBeanArrayAndTable(array($b1, $b2, $b3), $tableName);
    }//getInsertCollectionOfBeanWithTable

    /**
     * @param array $params
     * @return object[]
     */
    public function getArrayResults($params)
    {
        return $this->getAsArray($this->query, $params);
    }//getArrayResults

    public function writeSomething($params)
    {
        $this->write($this->query, $params);
    }//writeSomething
}//TestDAO

/**
 * Class EasySQLTest
 * @package com\github\elchris\easysql
 */
class EasySQLBaseTest extends EasySQLUnitTest
{
    const MYBEAN_INSERT = 'INSERT INTO mybean (`propOne`, `propTwo`) VALUES (:propOne, :propTwo);';
    const MYBEAN_COLLECTION_INSERT = 'INSERT INTO mybean (`propOne`,`propTwo`) VALUES (?,?),(?,?),(?,?);';
    const MYBEAN_COLLECTION_INSERT_YOURBEAN = 'INSERT INTO yourbean (`propOne`,`propTwo`) VALUES (?,?),(?,?),(?,?);';

    /**
     * @expectedException Exception
     */
    public function testReaDaoThrowsException()
    {
        $t = new RealDAO(new EasySQLContext());
        $t->getStuff();
    }//testReaDaoThrowsException

    public function testConnectionCredentialsForReadQuery()
    {
        $test = new TestDAO(new EasySQLContext(), $this->getReadQuery());
        $u = $test->getTestConnectionForQuery()->getUsername();
        $this->assertEquals('uname1slave', $u);
        $cs = $test->getTestConnectionForQuery()->getConnectionString();
        $this->assertEquals('mysql:host=slaveconnection1.host;dbname=name1', $cs);
    }//testConnectionCredentialsForReadQuery

    /**
     * @return string
     */
    private function getReadQuery()
    {
        return EasySQLQueryAnalyzer::SELECT . ' * FROM blah WHERE huh=2';
    }//testUnconfiguredNameThrowsException

    /**
     * @expectedException Exception
     */
    public function testUnconfiguredNameThrowsException()
    {
        $test = new TestDAO(new EasySQLContext(), $this->getReadQuery(), 'SomeNotConfiguredName');
        $this->assertEquals('SomeNotConfiguredName', $test->getApplicationName());
        $test->getTestConnectionForQuery();
    }//testSelectIsSlave

    public function testSelectIsSlave()
    {
        $test = new TestDAO(new EasySQLContext(), $this->getReadQuery());
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_SLAVE, $test->testDbType());
        $this->assertTrue($test->testIsRead());
    }//testUpdateIsMaster

    public function testUpdateIsMaster()
    {
        $test = new TestDAO(new EasySQLContext(), $this->getUpdateQuery());
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_MASTER, $test->testDbType());
        $this->assertFalse($test->testIsRead());
    }//testInsertIsMaster

    /**
     * @return string
     */
    private function getUpdateQuery()
    {
        return EasySQLQueryAnalyzer::UPDATE . ' * from blah where huh=2';
    }//testDeleteIsMaster

    public function testInsertIsMaster()
    {
        $test = new TestDAO(new EasySQLContext(), $this->getInsertQuery());
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_MASTER, $test->testDbType());
        $this->assertFalse($test->testIsRead());
    }//testCallIsMaster

    /**
     * @return string
     */
    private function getInsertQuery()
    {
        return EasySQLQueryAnalyzer::INSERT . ' * FROM blah WHERE huh=2';
    }//testReadQueryConnectionReuse

    public function testDeleteIsMaster()
    {
        $test = new TestDAO(new EasySQLContext(), $this->getDeleteQuery());
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_MASTER, $test->testDbType());
        $this->assertFalse($test->testIsRead());
    }//testWriteQueryConnectionResuse

    /**
     * @return string
     */
    private function getDeleteQuery()
    {
        return EasySQLQueryAnalyzer::DELETE . ' * FROM blah WHERE huh=2';
    }//testReadWriteConnectionsAreDifferent

    public function testCallIsMaster()
    {
        $test = new TestDAO(new EasySQLContext(), $this->getCallQuery());
        $this->assertEquals(EasySQLQueryAnalyzer::CONNECTION_MASTER, $test->testDbType());
        $this->assertFalse($test->testIsRead());
    }//testGotArrayOfMyBean

    /**
     * @return string
     */
    private function getCallQuery()
    {
        return EasySQLQueryAnalyzer::CALL . ' some_sproc()';
    }//testIsTestModeIsTrue

    public function testReadQueryConnectionReuse()
    {
        $ctx = new EasySQLContext();
        $test_one = new TestDAO($ctx, $this->getReadQueryTwo());
        $test_two = new TestDAO($ctx, $this->getReadQuery());
        $id_one = $test_one->getTestConnectionForQuery()->getId();
        $id_two = $test_two->getTestConnectionForQuery()->getId();
        $this->assertTrue($id_one === $id_two);
    }//testGotGenericArray

    /**
     * @return string
     */
    private function getReadQueryTwo()
    {
        return EasySQLQueryAnalyzer::SELECT . ' * FROM blah WHERE huh=1';
    }//testWriteSomething

    public function testWriteQueryConnectionReuse()
    {
        $ctx = new EasySQLContext();
        $test_three = new TestDAO($ctx, $this->getUpdateQuery());
        $test_four = new TestDAO($ctx, $this->getDeleteQuery());
        $test_five = new TestDAO($ctx, $this->getInsertQuery());

        $id_three = $test_three->getTestConnectionForQuery()->getId();
        $id_four = $test_four->getTestConnectionForQuery()->getId();
        $id_five = $test_five->getTestConnectionForQuery()->getId();

        $this->assertTrue($id_three === $id_four);
        $this->assertTrue($id_four === $id_five);
    }//testBeanInsertionWithoutTableName

    public function testReadWriteConnectionsAreDifferent()
    {
        $ctx = new EasySQLContext();
        $test_one = new TestDAO($ctx, $this->getReadQuery());
        $test_two = new TestDAO($ctx, $this->getReadQueryTwo());
        $test_three = new TestDAO($ctx, $this->getUpdateQuery());
        $test_four = new TestDAO($ctx, $this->getDeleteQuery());
        $test_five = new TestDAO($ctx, $this->getInsertQuery());

        $id_one = $test_one->getTestConnectionForQuery()->getId();
        $id_two = $test_two->getTestConnectionForQuery()->getId();
        $id_three = $test_three->getTestConnectionForQuery()->getId();
        $id_four = $test_four->getTestConnectionForQuery()->getId();
        $id_five = $test_five->getTestConnectionForQuery()->getId();

        $this->assertTrue($id_one === $id_two);
        $this->assertTrue($id_three === $id_four);
        $this->assertTrue($id_four === $id_five);
        $this->assertTrue($id_one !== $id_four);
    }//testBeanInsertionWithoutTableName

    public function testGotArrayOfMyBean()
    {
        $ctx = new EasySQLContext();
        $test = new TestDAO($ctx, $this->getReadQuery());
        $results = $test->getMyBeanCollection(array('one', 'two', 'three'));
        $this->assertNotNull($results);
        $this->assertNotEmpty($results);
        foreach ($results as $result) {
            $this->assertInstanceOf('com\github\elchris\easysql\IEasySQLBean', $result);
            $this->assertInstanceOf('com\github\elchris\easysql\tests\MyBean', $result);
        }
        $results = $test->getMyBeanCollection(array('key1' => 'value1', 'key2' => 'value2'));
        $this->assertNotNull($results);
        $this->assertNotEmpty($results);
        foreach ($results as $result) {
            $this->assertInstanceOf('com\github\elchris\easysql\IEasySQLBean', $result);
            $this->assertInstanceOf('com\github\elchris\easysql\tests\MyBean', $result);
        }
    }//testInsertCollectionOfBeans

    public function testIsTestModeIsTrue()
    {
        $test = new TestDAO(new EasySQLContext(), $this->getReadQuery());
        $this->assertTrue($test->isTest());
    }//testInsertCollectionOfBeansWithTableName

    public function testGotGenericArray()
    {
        $test = new TestDAO(new EasySQLContext(), $this->getReadQuery());
        $p = array('one', 'two', 'three');
        $p[3] = null;
        $results = $test->getArrayResults($p);
        $this->assertEquals(3, count($results));
        $p2 = array('key1' => 'value1', 'key2' => 'value2');
        $p2['key3'] = null;
        $results = $test->getArrayResults($p2);
        $this->assertEquals(3, count($results));
        $statementStash = $test->getTestConnectionForQuery()->getStatementStash();
        $this->assertArrayHasKey('SELECT * FROM blah WHERE huh=2', $statementStash);
    }

    public function testWriteSomething()
    {
        $test = new TestDAO(new EasySQLContext(), $this->getWriteQuery());
        $test->writeSomething(array('one', 'two', 'three'));
        $connection = $test->getTestConnectionForQuery();
        $this->assertInstanceOf('com\github\elchris\easysql\IEasySQLDB', $connection);
    }

    private function getWriteQuery()
    {
        return $this->getInsertQuery();
    }

    public function testBeanInsertionWithoutTableName()
    {
        $test = new TestDAO(new EasySQLContext(), $this->getInsertQuery());
        $values = $test->getMyBeanInsert();
        $this->assertEquals(self::MYBEAN_INSERT, $values[1]);
        $this->assertArrayHasKey('propOne', $values[0]);
        $this->assertArrayHasKey('propTwo', $values[0]);
        $this->assertEquals('somethingOne', $values[0]['propOne']);
        $this->assertEquals('somethingTwo', $values[0]['propTwo']);
    }

    public function testBeanInsertionWithTableName()
    {
        $test = new TestDAO(new EasySQLContext(), $this->getInsertQuery());
        $values = $test->getMyBeanInsertWithTableName();
        $this->assertEquals(self::MYBEAN_INSERT, $values[1]);
        $this->assertArrayHasKey('propOne', $values[0]);
        $this->assertArrayHasKey('propTwo', $values[0]);
        $this->assertEquals('somethingOne', $values[0]['propOne']);
        $this->assertEquals('somethingTwo', $values[0]['propTwo']);
    }//getWriteQuery

    public function testInsertCollectionOfBeans()
    {
        $test = new TestDAO(new EasySQLContext(), $this->getInsertQuery());
        $results = $test->getInsertCollectionOfBean();
        $this->assertEquals(self::MYBEAN_COLLECTION_INSERT, $results[1]);
        $this->assertEquals('somethingOne Row 1', $results[0][0]);
        $this->assertEquals('somethingTwo Row 1', $results[0][1]);
        $this->assertEquals('somethingOne Row 2', $results[0][2]);
        $this->assertEquals('somethingTwo Row 2', $results[0][3]);
        $this->assertEquals('somethingOne Row 3', $results[0][4]);
        $this->assertEquals('somethingTwo Row 3', $results[0][5]);
    }

    public function testInsertCollectionOfBeansWithTableName()
    {
        $test = new TestDAO(new EasySQLContext(), $this->getInsertQuery());
        $results = $test->getInsertCollectionOfBean('yourbean');
        $this->assertEquals(self::MYBEAN_COLLECTION_INSERT_YOURBEAN, $results[1]);
        $this->assertEquals('somethingOne Row 1', $results[0][0]);
        $this->assertEquals('somethingTwo Row 1', $results[0][1]);
        $this->assertEquals('somethingOne Row 2', $results[0][2]);
        $this->assertEquals('somethingTwo Row 2', $results[0][3]);
        $this->assertEquals('somethingOne Row 3', $results[0][4]);
        $this->assertEquals('somethingTwo Row 3', $results[0][5]);
    }//testReadWriteConnectionsAreDifferent
}//EasySQLBaseTest
 
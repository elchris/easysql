<?php
/**
 * Created with PhpStorm.
 * User: chris
 * Date: 11/23/13
 * Time: 5:25 PM
 */

namespace com\github\elchris\easysql\tests;

use com\github\elchris\easysql\EasySQLQueryAnalyzer;
use com\github\elchris\easysql\EasySQLConfig;
use com\github\elchris\easysql\EasySQL;
use com\github\elchris\easysql\EasySQLConnectionManager;
use com\github\elchris\easysql\EasySQLContext;
use com\github\elchris\easysql\IEasySQLConnectionManager;
use com\github\elchris\easysql\IEasySQLDB;
use Exception;

class FakeEasySQLConnectionManager extends EasySQLConnectionManager
{

}

class EasySQLConnectionManagerUnitTest extends EasySQLUnitTest
{
	const TEST_CONFIG_JSON = '{"test_application1":{"master":{"string":"mysql:host=masterconnection1.host;dbname=name1","u":"uname1master","p":"pass1master","connection":null},"slave":{"string":"mysql:host=slaveconnection1.host;dbname=name1","u":"uname1slave","p":"pass1slave","connection":null}}}';
    public function getTestConfig()
    {
            $fig = new EasySQLConfig();
            $fig
                ->addApplication('test_application1')
                ->setMaster('masterconnection1.host','name1','uname1master','pass1master')
                ->setSlave('slaveconnection1.host','name1','uname1slave','pass1slave')
            ;
            return $fig;
    }

    /**
     * @expectedException Exception
     */
    public function testNoConfigThrowsException()
    {
        $m = new FakeEasySQLConnectionManager(new EasySQLContext(), true);
        $m->deleteConfig();
        $m->getCurrentConfig(true);
    }//testNoConfigThrowsException

    public function testNoConfigGetNoDefaultConfig()
    {
        $m = new EasySQLConnectionManager(new EasySQLContext(), true);
        $m->deleteConfig();
        $this->assertFalse($m->isConfigured());
    }//testNoConfigGetDefaultConfig

	public function testSetNewConfig()
	{
		$m = new EasySQLConnectionManager(new EasySQLContext(), true);
		$m->setNewConfig($this->getTestConfig());
		$this->assertEquals(self::TEST_CONFIG_JSON, $m->getCurrentConfig()->getAsJson());
	}//testSetNewConfig

	/**
	 * @expectedException Exception
	 */
	public function testGetDbConnectionThrowsExceptionWithBadApplicationName()
	{
		$m = $this->getConnectionManager();
		$m->getDbConnection('something-not-configured','master');
		$m->releaseResources();
	}//testGetDbConnectionThrowsExceptionWithBadApplicationName

    /**
     * @expectedException Exception
     */
    public function testGetDbConnectionThrowsExceptionWithBadDbType()
    {
        $m = $this->getConnectionManager();
        $m->getDbConnection('test_application1','vassal');
        $m->releaseResources();
    }//testGetDbConnectionThrowsExceptionWithBadDbType

	public function testGetDbConnectionMasterWithCorrectApplicationName()
	{
		$m = $this->getConnectionManager();
		$masterConnection = $this->getMaster($m);
		$this->assertEquals('mysql:host=masterconnection1.host;dbname=name1',$masterConnection->getConnectionString());
		$m->releaseResources();
	}//testGetDbConnectionMasterWithCorrectApplicationName

	public function testGetDbConnectionSlaveWithCorrectApplicationName()
	{
		$m = $this->getConnectionManager();
		$slaveConnection = $this->getSlave($m);
		$this->assertEquals('mysql:host=slaveconnection1.host;dbname=name1',$slaveConnection->getConnectionString());
		$m->releaseResources();
	}//testGetDbConnectionSlaveWithCorrectApplicationName

	public function testSlaveConnectionsAreShared()
	{
		$m = $this->getConnectionManager();
		$slaveConnections = $this->getSharedConnectionsForType(EasySQLQueryAnalyzer::CONNECTION_SLAVE, $m);
		$this->verifyConnectionsAreShared($slaveConnections);
		$m->releaseResources();
	}//testSlaveConnectionsAreShared

	public function testMasterConnectionsAreShared()
	{
		$m = $this->getConnectionManager();
		$masterConnections = $this->getSharedConnectionsForType(EasySQLQueryAnalyzer::CONNECTION_MASTER, $m);
		$this->verifyConnectionsAreShared($masterConnections);
		$m->releaseResources();
	}//testMasterConnectionsAreShared

	public function testMasterAndSlaveConnectionsAreDifferent()
	{
		$m = $this->getConnectionManager();
		$slaveConnections = $this->getSharedConnectionsForType(EasySQLQueryAnalyzer::CONNECTION_SLAVE, $m);
		$masterConnections = $this->getSharedConnectionsForType(EasySQLQueryAnalyzer::CONNECTION_MASTER, $m);
		$counter = 0;
		foreach($slaveConnections as $slave) {
			$this->assertNotEquals($slave->getId(), $masterConnections[$counter]->getId());
			$counter++;
		}
		$m->releaseResources();
	}//testMasterAndSlaveConnectionsAreDifferent

	public function testReleaseResourcesWithAllActiveConnections()
	{
		$m = $this->getConnectionManager();
		$this->getSharedConnectionsForType(EasySQLQueryAnalyzer::CONNECTION_SLAVE, $m);
		$this->getSharedConnectionsForType(EasySQLQueryAnalyzer::CONNECTION_MASTER, $m);
		$releasedConnections = $m->releaseResources();
		$this->assertEquals(2, count($releasedConnections));
		$this->assertEquals('mysql:host=masterconnection1.host;dbname=name1',$releasedConnections[0]->getConnectionString());
		$this->assertEquals('mysql:host=slaveconnection1.host;dbname=name1',$releasedConnections[1]->getConnectionString());
	}//testReleaseResourcesWithAllActiveConnections

	public function testReleaseResourcesWithPartialActiveConnections()
	{
		$m = $this->getConnectionManager();
		$this->getSharedConnectionsForType(EasySQLQueryAnalyzer::CONNECTION_SLAVE, $m);
		$releasedConnections = $m->releaseResources();
		$this->assertEquals(1, count($releasedConnections));
		$this->assertEquals('mysql:host=slaveconnection1.host;dbname=name1',$releasedConnections[0]->getConnectionString());
	}//testReleaseResourcesWithPartialActiveConnections


	/**
	 * @return IEasySQLConnectionManager
	 */
	private function getConnectionManager()
	{
		$m = new EasySQLConnectionManager(new EasySQLContext(), true);
		$m->setNewConfig($this->getTestConfig());
		return $m;
	}//getConnectionManager

	/**
	 * @param IEasySQLConnectionManager $m
	 * @return IEasySQLDB
	 */
	private function getSlave(IEasySQLConnectionManager $m)
	{
		$slaveConnection = $m->getDbConnection('test_application1', EasySQLQueryAnalyzer::CONNECTION_SLAVE);
		return $slaveConnection;
	}//getSlave

	/**
	 * @param IEasySQLConnectionManager $m
	 * @return IEasySQLDB
	 */
	private function getMaster(IEasySQLConnectionManager $m)
	{
		$masterConnection = $m->getDbConnection('test_application1', EasySQLQueryAnalyzer::CONNECTION_MASTER);
		return $masterConnection;
	}//getMaster

	/**
	 * @param $connectionType
	 * @param IEasySQLConnectionManager $m
	 * @return IEasySQLDB[]
	 */
	private function getSharedConnectionsForType($connectionType, IEasySQLConnectionManager $m)
	{
		$connections = array();
		for ($i = 0; $i < 100; $i++) {
			usleep(10);
			array_push(
				$connections,
				$connectionType == EasySQLQueryAnalyzer::CONNECTION_SLAVE ? $this->getSlave($m) : $this->getMaster($m));
		}
		return $connections;
	}//getSharedConnectionsForType

	/**
	 * @param IEasySQLDB[] $connections
	 */
	private function verifyConnectionsAreShared($connections)
	{
		/**
		 * @var IEasySQLDB $testingConnection
		 * @var IEasySQLDB $theConnection
		 */
		$testingConnection = $connections[0];
		foreach ($connections as $theConnection) {
			$this->assertEquals($testingConnection->getId(), $theConnection->getId());
			$testingConnection = $theConnection;
		}
	}//verifyConnectionsAreShared
}//EasySQLConnectionManagerUnitTest
 
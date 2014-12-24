<?php
/**
 * Created with PhpStorm.
 * User: Chris Holland
 * Date: 12/4/14
 * Time: 11:55 AM
 */

namespace com\github\elchris\easysql;

use SebastianBergmann\Exporter\Exception;

class EasySQLConfigApplication
{
    const HOST = 'host';
    const DB = 'db';
    const U = 'u';
    const P = 'p';
    private $driver = null;
    private $master = null;
    private $slave = null;

    public function __construct($driver)
    {
        $this->driver = $driver;
        $this->master = array();
        $this->slave = array();
    }//EasySQLConfigApplication constructor

    public function setMaster($host, $db, $username, $password)
    {
        $this->setProps($this->master, $host, $db, $username, $password);
        if ($this->isEmpty($this->slave)) {
            $this->setProps($this->slave, $host, $db, $username, $password);
        }
        return $this;
    }//setProps

    private function setProps(&$arr, $host, $db, $u, $p)
    {
        $arr[self::HOST] = $host;
        $arr[self::DB] = $db;
        $arr[self::U] = $u;
        $arr[self::P] = $p;
    }//isEmpty

    private function isEmpty(&$arr)
    {
        return (count($arr, COUNT_RECURSIVE) === 0);
    }//setMaster

    public function setSlave($host, $db, $username, $password)
    {
        if ($this->isEmpty($this->master)) {
            throw new Exception('Please set a Master connection.');
        }
        $this->slave = array();
        $this->setProps($this->slave, $host, $db, $username, $password);
        return $this;
    }//setSlave

    public function getSlaveConnectionString()
    {
        return $this->driver . ':host=' . $this->slave[self::HOST] . ';dbname=' . $this->slave[self::DB];
    }//getSlaveConnectionString

    public function getSlaveUsername()
    {
        return $this->slave[self::U];
    }//getSlaveUsername

    public function getSlavePassword()
    {
        return $this->slave[self::P];
    }//getSlavePassword

    public function getMasterConnectionString()
    {
        return $this->driver . ':host=' . $this->master[self::HOST] . ';dbname=' . $this->master[self::DB];
    }//getMasterConnectionString

    public function getMasterUsername()
    {
        return $this->master[self::U];
    }//getMasterUsername

    public function getMasterPassword()
    {
        return $this->master[self::P];
    }//getMasterUsername
}//EasySQLConfigApplication

class EasySQLConfig
{
    const DRIVER_MYSQL = 'mysql';
    const KEY_CONNECTION = 'connection';

    /**
     * @var EasySQLConfigApplication[] $apps
     */
    private $apps = array();

    /**
     * @param string $name
     * @param string $driver
     * @return EasySQLConfigApplication
     */
    public function addApplication($name, $driver = self::DRIVER_MYSQL)
    {
        $newApp = new EasySQLConfigApplication($driver);
        $this->apps[$name] = $newApp;
        return $newApp;
    }//addApplication

    /**
     * @param $name
     * @return EasySQLConfigApplication
     */
    public function getApp($name)
    {
        return $this->apps[$name];
    }//getApp

    public function getAsJson()
    {
        return json_encode($this->getAsArray());
    }//getAsArray()

    /**
     * @return array
     */
    public function getAsArray()
    {
        $a = array();
        foreach ($this->apps as $appName => $appConfig) {
            $a[$appName] = array();
            $a[$appName][EasySQLQueryAnalyzer::CONNECTION_MASTER] = array();
            $a[$appName][EasySQLQueryAnalyzer::CONNECTION_MASTER][EasySQLConnectionManager::KEY_CONNECTION_STRING]
                = $appConfig->getMasterConnectionString();
            $a[$appName][EasySQLQueryAnalyzer::CONNECTION_MASTER][EasySQLConnectionManager::KEY_USERNAME]
                = $appConfig->getMasterUsername();
            $a[$appName][EasySQLQueryAnalyzer::CONNECTION_MASTER][EasySQLConnectionManager::KEY_PASSWORD]
                = $appConfig->getMasterPassword();
            $a[$appName][EasySQLQueryAnalyzer::CONNECTION_MASTER][self::KEY_CONNECTION] = null;

            $a[$appName][EasySQLQueryAnalyzer::CONNECTION_SLAVE] = array();
            $a[$appName][EasySQLQueryAnalyzer::CONNECTION_SLAVE][EasySQLConnectionManager::KEY_CONNECTION_STRING]
                = $appConfig->getSlaveConnectionString();
            $a[$appName][EasySQLQueryAnalyzer::CONNECTION_SLAVE][EasySQLConnectionManager::KEY_USERNAME]
                = $appConfig->getSlaveUsername();
            $a[$appName][EasySQLQueryAnalyzer::CONNECTION_SLAVE][EasySQLConnectionManager::KEY_PASSWORD]
                = $appConfig->getSlavePassword();
            $a[$appName][EasySQLQueryAnalyzer::CONNECTION_SLAVE][self::KEY_CONNECTION] = null;
        }
        return $a;
    }//getAsJson()
}//EasySQLConfig
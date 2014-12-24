<?php
/**
 * Created with PhpStorm.
 * User: Chris Holland
 * Date: 12/1/14
 * Time: 12:00 PM
 */

namespace com\github\elchris\easysql;


class EasySQLContext
{
    /**
     * @var IEasySQLDB[] $connections
     */
    private $connections = array();
    private $initialized = false;

    /**
     * @return IEasySQLDB[]
     */
    public function getConnections()
    {
        return $this->connections;
    }//getConnections

    public function setConnection($appName, $connectionType, IEasySQLDB $connection)
    {
        $this->connections[$appName][$connectionType][EasySQLConnectionManager::KEY_CONNECTION] = $connection;
    }//setConnection

    public function setConnectionConfig($key, $configTree)
    {
        $this->connections[$key] = $configTree;
    }//setConnectionConfig

    public function isInitialized()
    {
        return $this->initialized;
    }//isInitialized

    public function setInitialized()
    {
        $this->initialized = true;
    }//setInitialized

    public function unsetInitialized()
    {
        $this->initialized = false;
    }//unsetInitialized

    public function resetConnections()
    {
        $this->connections = null;
        $this->unsetInitialized();
    }//resetConnections
}//EasySQLContext
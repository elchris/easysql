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

    private $connections = array();
    private $initialized = false;

    /**
     * @return array
     */
    public function getConnections()
    {
        return $this->connections;
    }//getConnections

    /**
     * @return IEasySQLDB[]
     */
    public function releaseAndGetActiveStashedConnections()
    {
        /**
         * @var IEasySQLDB[] $allConnections;
         */
        $allConnections = array();
        foreach($this->connections as $appName => $appConnections) {
            $this->releaseAndGetStashedConnectionIfActive(
               $appConnections[EasySQLQueryAnalyzer::CONNECTION_MASTER],
               $allConnections
            );
            $this->releaseAndGetStashedConnectionIfActive(
                $appConnections[EasySQLQueryAnalyzer::CONNECTION_SLAVE],
                $allConnections
            );
        }
        return $allConnections;
    }//releaseAndGetActiveStashedConnections

    private function releaseAndGetStashedConnectionIfActive(&$connectionSpec, &$releasedConnections)
    {
        /**
         * @var IEasySQLDB $connection
         */
        if ($this->isConnectionActive($connectionSpec)) {
            $connection = $connectionSpec[EasySQLConnectionManager::KEY_CONNECTION];
            $connection->releaseResources();
            unset($connectionSpec[EasySQLConnectionManager::KEY_CONNECTION]);
            array_push($releasedConnections, $connection);
        }
    }//releaseAndGetStashedConnectionIfActive

    private function isConnectionActive(&$connectionSpec)
    {
        return
            isset($connectionSpec[EasySQLConnectionManager::KEY_CONNECTION])
            &&
            !is_null($connectionSpec[EasySQLConnectionManager::KEY_CONNECTION])
            &&
            $connectionSpec[EasySQLConnectionManager::KEY_CONNECTION] instanceof IEasySQLDB
            ;
    }

    /**
     * @param string $appName
     * @param string $connectionType
     * @return IEasySQLDB
     */
    public function getConnectionForAppType($appName, $connectionType)
    {
        if (isset($this->connections[$appName][$connectionType][EasySQLConnectionManager::KEY_CONNECTION])) {
            return $this->connections[$appName][$connectionType][EasySQLConnectionManager::KEY_CONNECTION];
        } else {
            return null;
        }
    }//getConnectionForAppType

    /**
     * @param string $appName
     * @param string $connectionType
     * @param IEasySQLDB $connection
     */
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

    public function resetConnections()
    {
        $this->connections = array();
        $this->unsetInitialized();
    }//unsetInitialized

    public function unsetInitialized()
    {
        $this->initialized = false;
    }//resetConnections
}//EasySQLContext
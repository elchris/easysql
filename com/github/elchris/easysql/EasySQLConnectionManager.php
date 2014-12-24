<?php

namespace com\github\elchris\easysql;
use SebastianBergmann\Exporter\Exception;

class EasySQLConnectionManager implements IEasySQLConnectionManager
{
    /**
     * @var EasySQLConfig $config
     */
    private static $config = null;
	const KEY_CONNECTION = 'connection';
    const KEY_USERNAME = 'u';
    const KEY_PASSWORD = 'p';
    const KEY_CONNECTION_STRING = 'string';

    private $mocked = false;
    private $context = null;

    public function __construct(EasySQLContext $context, $testMode = false)
	{
		$this->mocked = $testMode;
        $this->context = $context;
	}//EasySQLConnectionManager constructor

	public function setNewConfig(EasySQLConfig $config)
	{
        $this->deleteConfig();
		self::setConfig($config);
	}//setNewConfig

    public function deleteConfig()
    {
        $this->context->resetConnections();
        self::$config = null;
    }//deleteConfig

    /**
     * @param EasySQLConfig $config
     */
	private static function setConfig(EasySQLConfig $config)
	{
		self::$config = $config;
	}//setConfig

	public function getCurrentConfig()
	{
		if (!$this->isConfigured()) {
            throw new Exception('No configuration set');
		}
		return self::$config;
	}//getCurrentConfig

	private function init()
	{
		if (!$this->context->isInitialized()) {
			if (is_null($this->context->getConnections()) || (count($this->context->getConnections()) === 0)) {
				$this->initConnectionStubs();
			}
			$this->context->setInitialized();
		}
	}//init

	/**
	 * @param $string
	 * @param $u
	 * @param $p
	 * @return IEasySQLDB
	 */
	private function getNewConnection($string, $u, $p)
	{
		return new EasySQLDB($string, $u, $p, $this->mocked);
	}//getNewConnection

	private function initConnectionStubs()
	{
		if ($this->connectionsNotInitialized()) {
			$connectionConfigs = self::getCurrentConfig()->getAsArray();
			foreach ($connectionConfigs as $configName => $configTree) {
                $this->context->setConnectionConfig($configName, $configTree);
			}
		}//if connections not yet initialized
	}//initConnectionStubs

	public function getDbConnection($applicationName, $type)
	{
		$this->init();
        $this->checkForValidApplicationName($applicationName);
        $this->checkForValidConnectionType($applicationName, $type);
        $this->stashConnectionIfNoneAlreadyExists($applicationName, $type);
        return $this->context->getConnections()[$applicationName][$type][self::KEY_CONNECTION];
	}//getDbConnection

	public function releaseResources()
	{
		/**
		 * @var IEasySQLDB[] $releasedConnections
		 * @var IEasySQLDB $connection
		 */
		$releasedConnections = array();
		foreach ($this->context->getConnections() as $applicationName => $appConnections) {
			foreach ($appConnections as $masterOrSlave => $connectionSpec) {
				if ($this->isActiveConnection($connectionSpec)) {
					$connection = $connectionSpec[self::KEY_CONNECTION];
					$connection->releaseResources();
					unset($connectionSpec[self::KEY_CONNECTION]);
					array_push($releasedConnections, $connection);
				}//if connection is active, release its resources, and add it to collection
			}//loop thru master/slave configurations. should only be one of each
		}//loop thru configured applications
		return $releasedConnections;
	}//releaseResources

	/**
	 * @param $connectionSpec
	 * @return bool
	 */
	private function isActiveConnection($connectionSpec)
	{
		return isset($connectionSpec[self::KEY_CONNECTION])
		&&
		!is_null($connectionSpec[self::KEY_CONNECTION])
		&&
		$connectionSpec[self::KEY_CONNECTION] instanceof IEasySQLDB;
	}//isActiveConnection

    /**
     * @return bool
     */
    private function connectionsNotInitialized()
    {
        return is_null($this->context->getConnections()) || (count($this->context->getConnections()) === 0);
    }//connectionsNotInitialized

    /**
     * @param $applicationName
     * @param $type
     * @return IEasySQLDB
     */
    private function getNewConnectionForApplicationAndType($applicationName, $type)
    {
        return $this->getNewConnection(
            $this->context->getConnections()[$applicationName][$type][self::KEY_CONNECTION_STRING],
            $this->context->getConnections()[$applicationName][$type][self::KEY_USERNAME],
            $this->context->getConnections()[$applicationName][$type][self::KEY_PASSWORD]
        );
    }//getNewConnectionForApplicationAndType

	public function isConfigured(EasySQLConfig $referenceConfig = null)
    {
        $referenceMismatch = false;
        if (!is_null(self::$config) && !is_null($referenceConfig)) {
            $referenceMismatch = ($referenceConfig->getAsJson() !== self::$config->getAsJson());
        }
        return !$referenceMismatch && !is_null(self::$config);
    }//isConfigured

    /**
     * @param string $applicationName
     */
    private function checkForValidApplicationName($applicationName)
    {
        if (!isset($this->context->getConnections()[$applicationName])) {
            throw new Exception('Application Database: ' . $applicationName . ' is not defined');
        }
    }//checkForValidApplicationName

    /**
     * @param string $applicationName
     * @param string $type
     */
    private function checkForValidConnectionType($applicationName, $type)
    {
        if (!isset($this->context->getConnections()[$applicationName][$type])) {
            throw new Exception('Application Database Type: ' . $type . ' was not defined for ' . $applicationName);
        }
    }//checkForValidConnectionType

    /**
     * @param string $applicationName
     * @param string $type
     */
    private function stashConnectionIfNoneAlreadyExists($applicationName, $type)
    {
        if (is_null($this->context->getConnections()[$applicationName][$type][self::KEY_CONNECTION])) {
            $this->context->setConnection(
                $applicationName, $type,
                $this->getNewConnectionForApplicationAndType($applicationName, $type)
            );
        }
    }//stashConnectionIfNoneAlreadyExists
}//EasySQLConnectionManager
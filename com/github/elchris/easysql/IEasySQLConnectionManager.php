<?php
/**
 * Created with PhpStorm.
 * User: Chris Holland
 * Date: 12/23/14
 * Time: 6:55 PM
 */
namespace com\github\elchris\easysql;

interface IEasySQLConnectionManager
{
    /**
     * @param EasySQLConfig $config
     */
    public function setNewConfig(EasySQLConfig $config);

    public function deleteConfig();

    /**
     * @return EasySQLConfig
     */
    public function getCurrentConfig();

    /**
     * @param string $applicationName name of the application
     * @param string $type master or slave
     * @return IEasySQLDB
     */
    public function getDbConnection($applicationName, $type);

    /**
     * @return IEasySQLDB[]
     */
    public function releaseResources();

    /**
     * @param EasySQLConfig $referenceConfig
     * @return bool
     */
    public function isConfigured(EasySQLConfig $referenceConfig = null);
}
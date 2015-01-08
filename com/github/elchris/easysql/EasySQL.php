<?php
namespace com\github\elchris\easysql;

    /**
     * Created with PhpStorm.
     * User: chris
     * Date: 11/21/13
     * Time: 8:21 AM
     */

/**
 *
 * EasySQL aims to simplify certain aspects of working more optimally with SQL
 * from the following standpoints:
 *
 * a) Performance
 * b) Security
 * c) Working with Entities, henceforth referred to as "Beans"
 *
 * And more specifically:
 *
 * 1) It's typically somewhat painful to handle Master / Slave connection splitting within an application.
 * 2) There is a slight cost to the sequential opening and closing of connections across an execution path.
 * 2) a) Optimal management of connections lifecycle isn't trivial.
 * 2) b) Optimal management of prepared statement lifecycle isn't trivial.
 * 3) Prepared Statements are preferable for Security, yet working with them isn't trivial.
 * 4) Retrieving and Inserting strongly-typed Entities promotes more understandable and less brittle code
 * 4) a) But Entities should not have to be tied to tables when retrieving data
 * 4) b) Data-sets retrieved as untyped associative arrays add overhead to application development
 * 4) c) Efficient insert statements of multiple rows are often hard to achieve
 *
 * This library gives you transparent support for Master / Slave connection dispatching
 * while promoting more secure queries with Prepared Statements as well as the
 * reuse of prepared statements and an existing connection within an execution context.
 *
 * The underlying connector is PDO as it offers a consistent API for any RDBMS.
 * You may however drop-in anything which implements IEasySQLDB and IEasySQLDBStatement
 *
 * TODO: Make DI possible for swapping-in various implementations.
 *
 * Features:
 *
 * 1) This is not an ORM. It embraces SQL, PDO and Prepared Statements
 * 2) Simplify SELECTs and INSERTs working with "Entity Beans".
 * 3) Transparent support for Master / Slave query dispatching
 * 4) PDO object reuse to spawn less connections within an execution context
 * 5) Prepared Statement reuse for a given query string
 * 6) TODO: simplified parameter binding with addParam.
 * 7) TODO: select columns from bean
 * 8) TODO: Use Config to inject different implementations
 *
 *
 * @see http://php.net/manual/en/pdo.connections.php
 * @see http://php.net/manual/en/features.persistent-connections.php  <-- staying away from that.
 * @see http://php.net/manual/en/pdo.prepared-statements.php
 * @see http://php.net/manual/en/pdostatement.closecursor.php
 * @see http://stackoverflow.com/questions/23432948/fully-understanding-pdo-attr-persistent/23482353#comment36003085_23432948
 * @see http://stackoverflow.com/questions/19680494/insert-multiple-rows-with-pdo-prepared-statements
 * @see http://stackoverflow.com/questions/1176352/pdo-prepared-inserts-multiple-rows-in-single-query
 * @see http://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#sysvar_max_allowed_packet
 * @see http://php.net/manual/en/pdo.constants.php
 * @see http://dev.mysql.com/doc/index-other.html
 *
 *
 * Class EasySQL
 * @package com\github\elchris\easysql
 */
class EasySQL
{
    const NULL_STRING = 'NULL';

    /**
     * @var EasySQLContext $context
     * @var IEasySQLConnectionManager $manager
     * @var EasySQLConfig $config
     * @var EasySQLBeanQuery $beanQuery
     * @var EasySQLQueryAnalyzer $qAnalyzer
     */
    protected $context = null;
    protected $beanQuery = null;
    protected $qAnalyzer = null;
    protected $appName = null;
    protected $isTestMode = false;
    private $manager = null;
    private $config = null;

    /**
     * @param EasySQLConfig $config
     * @param EasySQLContext $context
     * @param string $appName
     */
    public function __construct(EasySQLContext $context, $appName, EasySQLConfig $config = null)
    {
        $this->context = $context;
        $this->appName = $appName;
        $this->config = $config;
        $this->beanQuery = $this->getNewEasySQLBeanQuery();
        $this->qAnalyzer = $this->getNewEasySQLQueryAnalyzer();
    }//EasySQL Constructor

    /**
     * Override this method for mock DAOs
     * @return bool
     */
    public function isTestMode()
    {
        return $this->isTestMode;
    }//isTestMode

    public function setTestMode()
    {
        $this->isTestMode = true;
    }//setTestMode

    /**
     * @param string $query
     * @param array $params
     * @return IEasySQLBean[]|\object[]
     * @throws \Exception
     */
    public function getAsArray($query, $params = null)
    {
        return $this->runQuery($query, $params);
    }//getAsArray

    /**
     * @param IEasySQLBean $emptyBean
     * @param string $query
     * @param array|null $params
     * @return IEasySQLBean[]
     * @throws \Exception
     */
    public function getAsCollectionOf(IEasySQLBean $emptyBean, $query, $params = null)
    {
        return $this->runQuery($query, $params, $emptyBean);
    }//getAsCollectionOf

    /**
     * @param string $query
     * @param array $params
     * @throws \Exception
     */
    public function write($query, $params = null)
    {
        $this->runQuery($query, $params, null, true);
    }//write

    /**
     * @param IEasySQLBean $bean
     * @param string $tableName
     * @throws \Exception
     */
    public function insertSingleBean(IEasySQLBean $bean, $tableName = null)
    {
        list($props, $insertQuery) = $this->beanQuery->getInsertQueryAndPropsForBeanTable($bean, $tableName);
        $this->runQuery($insertQuery, $props, null, true);
    }//insertSingleBean

    /**
     * @param IEasySQLBean[] $beanArray
     * @param string $tableName
     * @throws \Exception
     */
    public function insertCollectionOfBeans($beanArray, $tableName = null)
    {
        list($values, $q) = $this->beanQuery->getInsertQueryAndPropsForBeanArrayAndTable($beanArray, $tableName);
        $this->runQuery($q, $values, null, true);
    }//insertCollectionOfBeans

    /**
     * @return EasySQLBeanQuery
     */
    protected function getNewEasySQLBeanQuery()
    {
        return new EasySQLBeanQuery();
    }//getNewEasySQLBeanQuery

    /**
     * @return EasySQLQueryAnalyzer
     */
    protected function getNewEasySQLQueryAnalyzer()
    {
        return new EasySQLQueryAnalyzer();
    }//getNewEasySQLQueryAnalyzer

    /**
     * @param string $query
     * @param array $params
     * @param IEasySQLBean|null $emptyBean
     * @param bool $isWrite is query a write query
     * @return IEasySQLBean[]|\object[]
     */
    private function runQuery($query, $params, $emptyBean = null, $isWrite = false)
    {
        $connection = $this->getQueryConnection($query);
        $stmt = $connection->prepareQuery($query);
        $this->bindParams($params, $stmt);
        $stmt->execute();
        if (!$isWrite) {
            if (is_null($emptyBean)) {
                return $stmt->fetchAsCollection();
            } else {
                return $stmt->fetchAsCollectionOf($emptyBean);
            }
        } else {
            $stmt->afterQuery();
            return null;
        }
    }//runQuery

    /**
     * @param string $query
     * @return IEasySQLDB
     */
    protected function getQueryConnection($query)
    {
        return $this->getConnection($this->getApplicationName(), $this->qAnalyzer->getDbType($query));
    }//getQueryConnection

    /**
     * @param $db
     * @param $type
     * @return IEasySQLDB
     */
    private function getConnection($db, $type)
    {
        $this->manager = $this->getNewEasySQLConnectionManager();
        if (!$this->manager->isConfigured($this->config)) {
            $this->manager->setNewConfig($this->config);
        }
        return $this->manager->getDbConnection($db, $type);
    }//getConnection

    /**
     * @return IEasySQLConnectionManager
     */
    protected function getNewEasySQLConnectionManager()
    {
        return new EasySQLConnectionManager($this->getContext(), $this->isTestMode());
    }//getNewEasySQLConnectionManager

    /**
     * @return EasySQLContext
     */
    private function getContext()
    {
        return $this->context;
    }//getContext

    /**
     * @return string
     */
    protected function getApplicationName()
    {
        return $this->appName;
    }//getApplicationName

    /**
     * @param array $params
     * @param IEasySQLDBStatement $stmt
     */
    private function bindParams($params, IEasySQLDBStatement $stmt)
    {
        if (!is_null($params)) {
            if (isset($params[0])) {
                $this->bindIndexedParameters($params, $stmt);
            }//if bindParams are just a list of namedValuePairs
            else {
                foreach ($params as $key => $value) {
                    $this->bindNamedParameters($stmt, $value, $key);
                }//loop thru each param to bind
            }//parameters are named key/value pairs
        }//if bindParams were set with the query
    }//bindParams

    /**
     * @param array $params
     * @param IEasySQLDBStatement $stmt
     */
    private function bindIndexedParameters($params, IEasySQLDBStatement $stmt)
    {
        foreach ($params as $index => $value) {
            if (isset($value) && !is_null($value)) {
                $stmt->bindValueByIndex(($index + 1), $value);
            } else {
                $stmt->bindValueByIndex(($index + 1), self::NULL_STRING);
            }
        }//loop thru each param to bind
    }//bindIndexedParameters

    /**
     * @param IEasySQLDBStatement $stmt
     * @param $value
     * @param $key
     */
    private function bindNamedParameters(IEasySQLDBStatement $stmt, $value, $key)
    {
        if (isset($value) && !is_null($value)) {
            $stmt->bindValueByName($key, $value);
        } else {
            $stmt->bindValueByName($key, self::NULL_STRING);
        }
    }//bindNamedParameters
}//EasySQL
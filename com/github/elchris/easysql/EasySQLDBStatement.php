<?php
/**
 * Created with PhpStorm.
 * User: chris
 * Date: 11/21/13
 * Time: 8:47 AM
 */

namespace com\github\elchris\easysql;
use PDO;
use PDOStatement;
use Exception;


class EasySQLDBStatement implements IEasySQLDBStatement {

	/**
	 * @var bool $isMocked
	 * @var PDOStatement $statement
	 * @var string[string] $nameValueBinds
	 * @var string[int] $indexValueBinds
	 * @var bool $busy
	 * @var string $statementId
	 */
	private $isMocked = false;
	private $statement = null;
	private $nameValueBinds = array();
	private $indexValueBinds = array();
	private $busy = false;
	private $statementId = null;
	private static $statementCounter = 0;

	public function __construct(\PDOStatement &$passedStatement, $mocked = false)
	{
		$this->isMocked = $mocked;
		$this->statement = $passedStatement;
		$this->statementId = microtime().' '.++self::$statementCounter;
	}//EasySQLDBStatement

	/**
	 * @param string $name
	 * @param string $value
	 * @return void
	 */
	public function bindValueByName($name, $value)
	{
		$this->nameValueBinds[$name] = $value;
		if (!$this->isMocked) {
			$this->statement->bindValue($name, $value);
		}
	}//bindValueByName

	/**
	 * @param string $index
	 * @param int $value
	 * @return void
	 */
	public function bindValueByIndex($index, $value)
	{
		$this->indexValueBinds[$index] = $value;
		if (!$this->isMocked) {
			$this->statement->bindValue($index, $value);
		}
	}//bindValueByIndex

	/**
	 * @return array|\object[]
	 */
	public function fetchAsCollection()
	{
		$resultSet = $this->statement->fetchAll(PDO::FETCH_ASSOC);
		$this->afterQuery();
		return $resultSet;
	}//fetchAsCollection

	/**
	 * @param IEasySQLBean $emptyBeanInstance
	 * @return array|IEasySQLBean[]
	 */
	public function fetchAsCollectionOf(IEasySQLBean $emptyBeanInstance)
	{
		$resultSet = $this->statement->fetchAll(PDO::FETCH_CLASS, $emptyBeanInstance->getClassName());
        $this->afterQuery();
		return $resultSet;
	}//fetchAsCollectionOf

	/**
	 * @return void
	 * @throws Exception
	 */
	public function execute()
	{
		$this->beforeQuery();
		try {
			if (!$this->isMocked) {
				$this->statement->execute();
			}
		} catch (Exception $e) {
			$this->releaseResources();
			throw $e;
		}
	}//execute

	/**
	 * @return array
	 */
	public function getNameValueBinds()
	{
		return $this->nameValueBinds;
	}//getNameValueBinds

	/**
	 * @return array
	 */
	public function getIndexValueBinds()
	{
		return $this->indexValueBinds;
	}//getIndexValueBinds

	/**
	 * @return void
	 */
	public function releaseResources()
	{
		$this->afterQuery();
		$this->statement = null;
	}//releaseResources

	/**
	 * @return void
	 */
	public function beforeQuery()
	{
		$this->busy = true;
	}//beforeQuery

	/**
	 * @return void
	 */
	public function afterQuery()
	{
		if(!$this->isMocked) {
			$this->statement->closeCursor();
		}
		$this->busy = false;
	}//afterQUery

	/**
	 * @return boolean
	 */
	public function isBusy()
	{
		return $this->busy;
	}//isBusy

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->statementId;
	}//getId()
}//EasySQLDBStatement
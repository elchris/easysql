<?php
namespace com\github\elchris\easysql;
use com\github\elchris\easysql\IEasySQLDBStatement;

/**
 * Created with PhpStorm.
 * User: chris
 * Date: 11/21/13
 * Time: 8:19 AM
 */
interface IEasySQLDB
{
	/**
	 * @return string
	 */
	public function getId();

	/**
	 * @return string
	 */
	public function getUsername();

	/**
	 * @return string
	 */
	public function getConnectionString();

	/**
	 * @param string $query
	 * @return IEasySQLDBStatement
	 */
	public function prepareQuery($query);

	/**
	 * @return IEasySQLDBStatement[]
	 */
	public function getStatementStash();

	/**
	 * @return IEasySQLDBStatement[]
	 */
	public function releaseResources();
}
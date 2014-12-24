<?php
namespace com\github\elchris\easysql;
/**
 * Created with PhpStorm.
 * User: chris
 * Date: 11/21/13
 * Time: 8:20 AM
 */
interface IEasySQLDBStatement
{
	/**
	 * @return string
	 */
	public function getId();

	/**
	 * @param string $name
	 * @param string $value
	 * @return void
	 */
	public function bindValueByName($name, $value);

	/**
	 * @param string $index
	 * @param int $value
	 * @return void
	 */
	public function bindValueByIndex($index, $value);

	/**
	 * @return array
	 */
	public function getNameValueBinds();

	/**
	 * @return array
	 */
	public function getIndexValueBinds();


	/**
	 * @return object[]
	 */
	public function fetchAsCollection();

	/**
	 * @param IEasySQLBean $emptyBeanInstance
	 * @return IEasySQLBean[]
	 */
	public function fetchAsCollectionOf(IEasySQLBean $emptyBeanInstance);

	/**
	 * @return void
	 */
	public function execute();

	/**
	 * @return void
	 */
	public function beforeQuery();

	/**
	 * @return void
	 */
	public function afterQuery();

	/**
	 * @return boolean
	 */
	public function isBusy();

	/**
	 * @return void
	 */
	public function releaseResources();
}//IEasySQLDBStatement
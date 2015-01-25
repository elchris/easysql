<?php
/**
 * Created with PhpStorm.
 * User: chris
 * Date: 11/21/13
 * Time: 1:00 PM
 */

namespace com\github\elchris\easysql;

class MockEasySQLDBStatement
    extends EasySQLDBStatement
    implements IEasySQLDBStatement
{

    public function __construct()
    {
        parent::__construct(new \PDOStatement(), true);
    }//MockEasySQLDBStatement constructor

    /**
     * @return array()|\object[]
     */
    public function fetchAsCollection()
    {
        $resultSet = array(array(), array(), array());
        parent::afterQuery();
        return $resultSet;
    }//fetchAsCollection

    /**
     * @param IEasySQLBean $emptyBeanInstance
     * @return IEasySQLBean[]
     */
    public function fetchAsCollectionOf(IEasySQLBean $emptyBeanInstance)
    {
        $className = $emptyBeanInstance->getClassName();
        $resultSet = array(new $className, new $className, new $className);
        parent::afterQuery();
        return $resultSet;
    }//fetchAsCollectionOf
}//MockEasySQLDBStatement
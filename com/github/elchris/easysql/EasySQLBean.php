<?php
/**
 * Created with PhpStorm.
 * User: chris
 * Date: 11/23/13
 * Time: 5:06 PM
 */

namespace com\github\elchris\easysql;


abstract class EasySQLBean implements IEasySQLBean
{

    private $className = null;

    /**
     * @return \ReflectionClass
     */
    public function getReflectionClass()
    {
        return new \ReflectionClass($this->getClassName());
    }//getClassName

    /**
     * @return string
     */
    public function getClassName()
    {
        if (is_null($this->className)) {
            $this->className = get_class($this);
        }
        return $this->className;
    }//getReflectionClass
}//EasySQLBean
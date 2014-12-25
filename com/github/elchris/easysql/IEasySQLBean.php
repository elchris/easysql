<?php
/**
 * Created with PhpStorm.
 * User: chris
 * Date: 11/21/13
 * Time: 1:10 PM
 */

namespace com\github\elchris\easysql;

/**
 *
 * Marker interface for now
 *
 * Interface IEasySQLBean
 * @package com\github\elchris\easysql
 */
interface IEasySQLBean
{
    /**
     * @return string
     */
    public function getClassName();

    /**
     * @return \ReflectionClass
     */
    public function getReflectionClass();
}//IEasySQLBean
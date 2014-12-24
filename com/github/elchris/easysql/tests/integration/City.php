<?php
/**
 * Created with PhpStorm.
 * User: Chris Holland
 * Date: 12/24/14
 * Time: 12:09 AM
 */
namespace com\github\elchris\easysql\tests\integration;

use com\github\elchris\easysql\EasySQLBean;

class City extends EasySQLBean
{
    public $ID;
    public $Name;
    public $CountryCode;
    public $District;
    public $Population;
}
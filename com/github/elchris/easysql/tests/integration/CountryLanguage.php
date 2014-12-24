<?php
/**
 * Created with PhpStorm.
 * User: Chris Holland
 * Date: 12/24/14
 * Time: 12:10 AM
 */
namespace com\github\elchris\easysql\tests\integration;

use com\github\elchris\easysql\EasySQLBean;

class CountryLanguage extends EasySQLBean
{
    public $CountryCode;
    public $Language;
    public $IsOfficial;
    public $Percentage;
}
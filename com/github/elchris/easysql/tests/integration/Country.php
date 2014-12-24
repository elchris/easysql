<?php
/**
 * Created with PhpStorm.
 * User: Chris Holland
 * Date: 12/24/14
 * Time: 12:09 AM
 */
namespace com\github\elchris\easysql\tests\integration;

use com\github\elchris\easysql\EasySQLBean;

class Country extends EasySQLBean
{
    public $Code;
    public $Name;
    public $Continent;
    public $Region;
    public $SurfaceArea;
    public $IndepYear;
    public $Population;
    public $LifeExpectancy;
    public $GNP;
    public $GNPOld;
    public $LocalName;
    public $GovernmentFrom;
    public $HeadOfState;
    public $Capital;
    public $Code2;
}
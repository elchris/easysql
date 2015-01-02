<?php
/**
 * Created with PhpStorm.
 * User: Chris Holland
 * Date: 12/24/14
 * Time: 12:13 AM
 */
namespace com\github\elchris\easysql\tests\integration;

use com\github\elchris\easysql\EasySQL;
use com\github\elchris\easysql\EasySQLConfig;
use com\github\elchris\easysql\EasySQLContext;

class ExampleBaseModel
{
    const FAKE_COUNTRY = "INSERT INTO `Country` (`Code`, `Name`, `Continent`, `Region`, `SurfaceArea`, `IndepYear`, `Population`, `LifeExpectancy`, `GNP`, `GNPOld`, `LocalName`, `GovernmentForm`, `HeadOfState`, `Capital`, `Code2`)
VALUES ('FKE', 'Fakeland', 'Asia', 'South America', 1234.00, 1900, 1232345, 90.0, 12345.00, 12345.00, 'Fakeworld', 'Fakerepublic', 'Eric Cartman', 537, '');";
    const APP_DATABASE_NAME = 'world';
    const APP_USERNAME = 'root';
    const APP_PASSWORD = '';
    const APP_HOSTNAME = '127.0.0.1';

    /**
     * @var EasySQLConfig $config
     */
    private static $config = null;
    /**
     * @var EasySQLContext $context
     */
    private $context = null;

    public function __construct(EasySQLContext $ctx)
    {
        $this->context = $ctx;
    }

    public function db()
    {
        return new EasySQL($this->context, self::APP_DATABASE_NAME, self::getConfig());
    }//db

    private static function getConfig()
    {
        if (is_null(self::$config)) {
            $fig = new EasySQLConfig(EasySQLConfig::DRIVER_MYSQL);
            $fig->addApplication(self::APP_DATABASE_NAME)
                ->setMaster(self::APP_HOSTNAME, self::APP_DATABASE_NAME, self::APP_USERNAME, self::APP_PASSWORD);;
            self::$config = $fig;
        }
        return self::$config;
    }//getConfig
}//ExampleBaseModel
<?php
/**
 * Created with PhpStorm.
 * User: Chris Holland
 * Date: 12/9/14
 * Time: 10:47 PM
 */

namespace com\github\elchris\easysql\tests;

use com\github\elchris\easysql\EasySQL;
use com\github\elchris\easysql\EasySQLBean;
use com\github\elchris\easysql\EasySQLConfig;
use com\github\elchris\easysql\EasySQLContext;

class Book extends EasySQLBean
{
    public $title;
    public $author;
    public $price;
}

class MyService
{
    private static $config = null;
    /**
     * @var EasySQLContext $context
     */
    private $context = null;//getDbConfig

    public function __construct(EasySQLContext $context)
    {
        $this->context = $context;
    }

    /**
     * @param string $title
     * @return Book[]
     */
    public function getBooksByTitle($title)
    {
        return $this
            ->getDb()
            ->getAsCollectionOf(
                new Book(),
                'SELECT * FROM books WHERE name="?";',
                array($title));
    }//MyService Constructor

    private function getDb()
    {
        $sql = new EasySQL($this->context, 'myapplication1', self::getDbConfig());
        $sql->setTestMode();
        return $sql;
    }//getDb1

    private static function getDbConfig()
    {
        if (is_null(self::$config)) {
            $c = new EasySQLConfig(EasySQLConfig::DRIVER_MYSQL);
            $c
                ->addApplication('myapplication1')
                ->setMaster('app1localhost', 'app1db', 'app1myusername', 'app1mypassword');
            $c
                ->addApplication('myapplication2')
                ->setMaster('app2localhost', 'app2db', 'app2myusername', 'app2mypassword');
            self::$config = $c;
        }
        return self::$config;
    }//getBooksByTitle
}//MyService

class ApplicationTest extends EasySQLUnitTest
{
    public function testGetBooks()
    {
        $s = new MyService(new EasySQLContext());
        $books = $s->getBooksByTitle('Clean Code');
        foreach ($books as $book) {
            $this->assertInstanceOf('com\github\elchris\easysql\tests\Book', $book);
        }
    }//testGetBooks
}//ApplicationTest
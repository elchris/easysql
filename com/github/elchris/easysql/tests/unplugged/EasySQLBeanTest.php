<?php
/**
 * Created with PhpStorm.
 * User: chris
 * Date: 11/23/13
 * Time: 5:20 PM
 */

namespace com\github\elchris\easysql\tests;

use com\github\elchris\easysql\EasySQLBean;

class SomeBean extends EasySQLBean
{

}//EasySQLBean

class EasySQLBeanUnitTest extends EasySQLUnitTest
{

    public function testGetClassName()
    {
        $b = new SomeBean();
        $this->assertEquals(get_class($b), $b->getClassName());
    }//testGetClassName

}//EasySQLBeanUnitTest
 
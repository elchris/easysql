<?php
/**
 * Created with PhpStorm.
 * User: Chris Holland
 * Date: 12/8/14
 * Time: 12:47 AM
 */

namespace com\github\elchris\easysql\tests;


use com\github\elchris\easysql\EasySQL;
use com\github\elchris\easysql\EasySQLContext;

class EasySQLTest extends EasySQLUnitTest
{
    public function testIsTestMode()
    {
        $s = new EasySQL(new EasySQLContext(), 'application1');
        $s->setTestMode();
        $this->assertTrue($s->isTestMode());
    }

    public function testZeroConnectionCleanedUp()
    {
        $s = new EasySQL(new EasySQLContext(), 'application1');
        $s->setTestMode();
        $cleanedConnections = $s->cleanUp();
        $this->assertCount(0,$cleanedConnections);
    }
}//EasySQLTest
 
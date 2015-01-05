EasySQL
=======

Work more optimally with SQL for Performance, Security and Readability:

* Optimal management of PDO Connections and Prepared Statements across an execution context
* Transparent dispatching of queries to Master or Slave connection
* Strongly-typed INSERT Inputs and SELECT Outputs using Entity Beans
* Built TDD in PhpStorm with SOLID principles, 100% code coverage thru Unit and Integration Tests
* PSR-2 coding style
* PSR-4 auto-loading
* Composer Installation
* [Semantic Versioning](http://semver.org) will be followed

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/elchris/easysql/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/elchris/easysql/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/elchris/easysql/badges/build.png?b=master)](https://scrutinizer-ci.com/g/elchris/easysql/build-status/master)

More Precisely
==============

* This is not an ORM library. It assumes you'll be writing your own SQL queries.
* By leveraging "Entity Beans" for INSERT and SELECT operations, spend less time juggling untyped associative arrays.
* EasySQLConfig allows you to:
    * Define your PDO-supported driver, EasySQL::DRIVER_POSTGRES or EasySQL::DRIVER_MYSQL
    * Define multiple applications
    * Define a Master and a Slave connection for each application

Composer Install:
======================

https://packagist.org/packages/easysql/easysql

    {
        "require": {
            "easysql/easysql": "dev-master"
        }
    }

Sample Usage:
=============
* [Example "BaseModel" Class](https://github.com/elchris/easysql/blob/master/com/github/elchris/easysql/tests/integration/ExampleBaseModel.php)
* [Example "Model" Class]
(https://github.com/elchris/easysql/blob/master/com/github/elchris/easysql/tests/integration/ExampleApplicationTest.php)

Purpose:
========
https://github.com/elchris/easysql/blob/master/com/github/elchris/easysql/EasySQL.php#L13


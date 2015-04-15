EasySQL
=======

Work more effectively with SQL for Performance, Security and Code Readability:

* Exposes Simple API - Takes care of PDO and Prepared Statement management so you don't have to
* Strongly-Typed returns on SELECT operations, returning instances of Entity Beans
* Strongly-Typed INSERT operations allowing easy insertion of collections of Entity Beans
* First-Class support for Master and Slave connections, with transparent dispatching of queries to appropriate connection
* Optimal management of PDO Connections and Prepared Statements across an execution context
   * Reusable connections are managed via a shared "Execution Context", allowing you to use and reuse as many "Model" Class instances as convenient without constantly opening-up and tearing-down connections.
   * Each connection keeps track of "Prepared Statements" tied to each new "Query String" it sees. Should the same query be invoked via another method or class later within an Execution Context, the previously-generated Prepared Statement tied to that query will be reused.

[![Test Coverage](https://codeclimate.com/github/elchris/easysql/badges/coverage.svg)](https://codeclimate.com/github/elchris/easysql)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/elchris/easysql/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/elchris/easysql/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/elchris/easysql/badges/build.png?b=master)](https://scrutinizer-ci.com/g/elchris/easysql/build-status/master)

Compliance & Standards
======================

* Built [TDD](http://en.wikipedia.org/wiki/Test-driven_development) in [PhpStorm](https://www.jetbrains.com/phpstorm/) with [SOLID](http://en.wikipedia.org/wiki/SOLID_(object-oriented_design)) principles, 100% code coverage thru Unit and Integration Tests
* [PSR-2](http://www.php-fig.org/psr/psr-2/) coding style
* [PSR-4](http://www.php-fig.org/psr/psr-4/) auto-loading
* [Composer Installation](https://getcomposer.org) from [Packagist](https://packagist.org/packages/easysql/easysql)
* [Semantic Versioning](http://semver.org) will be followed

More Precisely
==============

* This is not an ORM library. It assumes you'll be writing your own SQL queries.
* By leveraging "Entity Beans" for INSERT and SELECT operations, spend less time juggling untyped associative arrays.
* EasySQLConfig allows you to:
    * Define your PDO-supported driver:
      * [EasySQL::DRIVER_POSTGRES](https://github.com/elchris/easysql/blob/master/com/github/elchris/easysql/EasySQLConfig.php#L96)
      * [EasySQL::DRIVER_MYSQL](https://github.com/elchris/easysql/blob/master/com/github/elchris/easysql/EasySQLConfig.php#L95)
    * Define multiple applications
    * Define a Master and a Slave connection for each application

Composer Installation:
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
* [Example "WorldModel" Class]
(https://github.com/elchris/easysql/blob/master/com/github/elchris/easysql/tests/integration/ExampleApplicationTest.php)

[Purpose](https://github.com/elchris/easysql/blob/master/com/github/elchris/easysql/EasySQL.php#L13)
========





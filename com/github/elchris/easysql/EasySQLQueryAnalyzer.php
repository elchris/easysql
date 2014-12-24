<?php
/**
 * Created with PhpStorm.
 * User: Chris Holland
 * Date: 12/19/14
 * Time: 4:30 PM
 */

namespace com\github\elchris\easysql;


class EasySQLQueryAnalyzer
{
    const CONNECTION_SLAVE = 'slave';
    const CONNECTION_MASTER = 'master';
    const SELECT = 'SELECT';
    const INSERT = 'INSERT';
    const UPDATE = 'UPDATE';
    const DELETE = 'DELETE';
    const CALL = 'CALL';

    public function getDbType($q)
    {
        if ($this->isReadQuery($q)) {
            return self::CONNECTION_SLAVE;
        } else {
            return self::CONNECTION_MASTER;
        }
    }//isReadQuery

    /**
     * @param $query
     * @return bool
     */
    public function isReadQuery($query)
    {
        // insert
        if (strripos($query, self::INSERT) !== false) {
            return false;
        }
        // udpate
        if (strripos($query, self::UPDATE) !== false) {
            return false;
        }
        // delete
        if (strripos($query, self::DELETE) !== false) {
            return false;
        }
        // sp calls
        if (strripos($query, self::CALL) !== false) {
            return false;
        }
        return true;
    }//getDbType
}//EasySQLQueryAnalyzer
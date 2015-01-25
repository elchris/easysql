<?php
/**
 * Created with PhpStorm.
 * User: chris
 * Date: 11/21/13
 * Time: 8:35 AM
 */

namespace com\github\elchris\easysql;

use Exception;
use PDO;

class EasySQLDB implements IEasySQLDB
{
    private static $connectionCounter = 0;
    /**
     * @var PDO $pdo
     * @var string $connectionId
     * @var bool $mocked
     * @var string $username
     * @var string $connectionString
     */
    private $pdo = null;
    private $connectionId = '0';
    private $mocked = false;
    private $username = null;
    private $connectionString = null;
    /**
     * @var IEasySQLDBStatement[string] $preparedStatementStash
     */
    private $preparedStatementStash = array();

    /**
     * @param string $connectionString
     * @param string $u
     * @param string $p
     * @param boolean $testMode
     * @throws Exception
     */
    public function __construct($connectionString, $u, $p, $testMode = false)
    {
        $this->connectionId = $connectionString . ' ' . microtime() . ' ' . ++self::$connectionCounter;
        $this->mocked = $testMode;
        $this->connectionString = $connectionString;
        $this->username = $u;
        if (!$this->mocked) {
            try {
                $this->pdo = new PDO($connectionString, $u, $p, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            } catch (Exception $e) {
                //don't let PDO leak out the pw:
                //Catch exception and throw-up a safer one.
                throw new Exception('Error connecting to: ' . $connectionString);
            }
        }
    }

    /**
     * @return string
     */
    public function getConnectionString()
    {
        return $this->connectionString;
    }//EasySQLDB Construct

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $query
     * @return IEasySQLDBStatement
     */
    public function prepareQuery($query)
    {
        return $this->getPreparedStatement($query);
    }//getPreparedStatement

    /**
     * @param $queryString
     * @return IEasySQLDBStatement
     */
    private function getPreparedStatement($queryString)
    {
        /**
         * @var IEasySQLDBStatement $statement
         */
        if (!isset($this->preparedStatementStash[$queryString])) {
            $this->stashNewStatement($queryString);
        }
        $statement = $this->preparedStatementStash[$queryString];
        if ($statement->isBusy()) {
            $statement->releaseResources();
            $this->stashNewStatement($queryString);
        }
        return $this->preparedStatementStash[$queryString];
    }//prepareQuery

    /**
     * @param $queryString
     */
    private function stashNewStatement($queryString)
    {
        if ($this->mocked) {
            $statementToStash = new MockEasySQLDBStatement();
        } else {
            $pdoStatement = $this->pdo->prepare($queryString);
            $statementToStash = new EasySQLDBStatement($pdoStatement);
        }
        $this->preparedStatementStash[$queryString] = $statementToStash;
    }//stashNewStatement

    /**
     * @return string
     */
    public function getId()
    {
        return $this->connectionId;
    }//getId

    /**
     * @return IEasySQLDBStatement[]
     */
    public function releaseResources()
    {
        /**
         * @var string $q
         * @var IEasySQLDBStatement $statement
         * @var IEasySQLDBStatement[] $releasedResources
         */
        $releasedResources = array();
        foreach ($this->preparedStatementStash as $q => $statement) {
            $statement->releaseResources();
            array_push($releasedResources, $statement);
        }
        $this->pdo = null;
        return $releasedResources;
    }//releaseResources

    /**
     * @return IEasySQLDBStatement[]
     */
    public function getStatementStash()
    {
        return $this->preparedStatementStash;
    }//getStatementStash
}//EasySQLDB

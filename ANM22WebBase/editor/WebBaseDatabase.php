<?php

/**
 * WebBase database object.
 * This class is inspired by ANM22 Mysql DB library version 1.3.0
 * 
 * @author Andrea Menghi <andrea.menghi@anm22.it>
 */
class WebBaseDatabase
{

    private $ipAddress;
    private $writingIpAddress;
    private $dbName;
    private $user;
    private $password;

    private $conn;

    private $debugMode = false;
    private $writingMode = false;

    public $insert_id = null;


    /**
     * Connection to the MySQL database. If the parameters are empty, the method doesn't open the connection.
     * 
     * @param string $ipAddress Database host
     * @param string $dbName Database name
     * @param string $user Database username
     * @param string $password Database user password
     * @param string $writingIpAddress Database writing host
     */
    public function __construct($ipAddress = null, $dbName = null, $user = null, $password = null, $writingIpAddress = null)
    {

        if (!is_null($ipAddress)) {
            $this->ipAddress = $ipAddress;
            $this->dbName = $dbName;
            $this->user = $user;
            $this->password = $password;
            $this->writingIpAddress = $writingIpAddress;
            if (is_null($this->writingIpAddress) || $this->ipAddress == $this->writingIpAddress) {
                $this->writingMode = true;
            }

            $this->conn = new mysqli($ipAddress, $user, $password, $dbName);

            $this->conn->set_charset("utf8mb4");
        }
    }

    /**
     * Open connection to ANM22 WebBase hosting DB
     * 
     * @return bool
     */
    public function connect()
    {
        include __DIR__ . "/../config/mysql.php";

        $this->ipAddress = $db_host;
        $this->dbName = $db_name;
        $this->user = $db_user;
        $this->password = $db_pass;
        $this->writingIpAddress = $db_host;
        if (is_null($this->writingIpAddress) || $this->ipAddress == $this->writingIpAddress) {
            $this->writingMode = true;
        }

        $this->conn = new mysqli($this->ipAddress, $this->user, $this->password, $this->dbName);

        if ($this->conn) {
            $this->conn->set_charset("utf8mb4");
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the database connection.
     * If it isn't init, the method returns null. If there is a connection error, the method returns the error message.
     * 
     * @return mysqli|null|string
     */
    public function getConn()
    {
        if ($this->conn->connect_errno) {
            return $this->conn->connect_errno;
        } else {
            return $this->conn;
        }
    }

    /**
     * Set the database connection
     * 
     * @param mysqli $mysqli Database connection
     * @return $this
     */
    public function setConn($mysqli)
    {
        $this->conn = $mysqli;
        return $this;
    }

    /** 
     * Open a connection to the database with writing mode.
     * 
     * @return bool
     */
    public function enableWriting()
    {
        if (!$this->conn || is_null($this->writingIpAddress)) {
            return false;
        }
        if ($this->ipAddress !== $this->writingIpAddress) {
            $this->conn->close();
            $this->conn = new mysqli($this->writingIpAddress, $this->user, $this->password, $this->dbName);
            $this->conn->set_charset("utf8mb4");
        }
        return true;
    }

    /**
     * Run the query and get the associative array of all results.
     * 
     * @param string $query
     * @return mixed[]|boolean
     */
    public function makeQueryAssoc($query)
    {
        if (!$res = $this->conn->query($query)) {
            if ($this->getDebugModeStatus()) {
                echo $query;
                printf("Errormessage: %s\n", $this->conn->error);
            }
            return false;
        }
        $result = $res->fetch_all(MYSQLI_ASSOC);
        return $result;
    }

    /**
     * Run the query and get the associative array of the first result.
     * 
     * @param string $query
     * @return mixed[]|boolean
     */
    public function makeQueryFetch($query)
    {
        if (!$res = $this->conn->query($query)) {
            if ($this->getDebugModeStatus()) {
                echo $query;
                printf("Errormessage: %s\n", $this->conn->error);
            }
            return false;
        }
        $result = $res->fetch_array(MYSQLI_ASSOC);
        return $result;
    }

    /**
     * Run the query.
     * 
     * @param string $query
     * @return mysqli_result|boolean
     */
    public function makeQuery($query)
    {
        if (!$res = $this->conn->query($query)) {
            if ($this->getDebugModeStatus()) {
                echo $query;
                printf("Errormessage: %s\n", $this->conn->error);
            }
            return false;
        }
        $this->insert_id = $this->conn->insert_id;
        return $res;
    }

    public function getName()
    {
        return $this->dbName;
    }

    /**
     * Get the ID of the last inserted roe.
     * 
     * @return integer
     */
    public function getLastInsertedId()
    {
        return $this->conn->insert_id;
    }

    public function getAllLinesFromTable($table)
    {

        $queryAllLines = "SELECT * "
            . "FROM `" . $table . "`";

        return $this->makeQueryAssoc($queryAllLines);
    }

    public function getAllFieldsFromTableViaUniqueId($tableName, $idColumnName, $id)
    {
        $tableNameNew = $this->getConn()->real_escape_string($tableName);
        $idColumnNameNew = $this->getConn()->real_escape_string($idColumnName);
        $idNew = intval($id);

        $query = "SELECT * "
            . "FROM `" . $tableNameNew . "` "
            . "WHERE `" . $idColumnNameNew . "` = '" . $idNew . "';";
        $res = $this->makeQueryFetch($query);
        return $res;
    }

    public function getAllFieldsFromTableViaUniqueIdJoin($table1, $col1, $table2, $col2, $id1)
    {
        $table1Clean = $this->getConn()->real_escape_string($table1);
        $col1Clean = $this->getConn()->real_escape_string($col1);
        $table2Clean = $this->getConn()->real_escape_string($table2);
        $col2Clean = $this->getConn()->real_escape_string($col2);
        $id1Clean = intval($id1);

        $query = "SELECT * "
            . "FROM `" . $table1Clean . "`, `" . $table2Clean . "` "
            . "WHERE `" . $col1Clean . "` = `" . $col2Clean . "` AND `" . $col1Clean . "` = '" . $id1Clean . "';";
        $res = $this->makeQueryFetch($query);
        return $res;
    }

    public function getAllFieldsFromTablesViaUniqueId($data)
    {
        for ($i = 0; $i < count($data['tables']); $i++) {
            $data['tables'][$i] = $this->getConn()->real_escape_string($data['tables'][$i]);
        }
        $whereStrings = array();
        for ($i = 0; $i < count($data['cols']); $i++) {
            $data['cols'][$i][0] = $this->getConn()->real_escape_string($data['cols'][$i][0]);
            $whereStrings[] = "`" . implode("` = '", $data['cols'][$i]) . "'";
        }

        $query = "SELECT * "
            . "FROM `" . implode("`, `", $data['tables']) . "` "
            . "WHERE " . implode(" AND ", $whereStrings) . ";";
        $res = $this->makeQueryFetch($query);
        return $res;
    }

    public function getAllLinesFromTablesWithJoinCondition($table1, $table2, $col1, $col2)
    {

        $queryAllLines = "SELECT * "
            . "FROM `" . $table1 . "`,`" . $table2 . "` "
            . "WHERE `" . $col1 . "` = `" . $col2 . "`;";

        return $this->makeQueryAssoc($queryAllLines);
    }

    /**
     * Run the escape command to the given parameters
     * 
     * @param mixed $value Value to be escaped
     * @return string
     */
    public function escape($value)
    {
        return $this->conn->real_escape_string("" . $value);
    }

    /**
     * Get the status of teh debug mode.
     * If it is enabled, in case of an MySQL error, the object print the query and the error message.
     * 
     * @return boolean
     */
    public function getDebugModeStatus()
    {
        return $this->debugMode;
    }

    /**
     * Set the status of the debug mode.
     * 
     * @param boolean $status Debug mode status
     * @return $this
     */
    public function setDebugModeStatus($status)
    {
        $this->debugMode = $status;
        return $this;
    }

    /**
     * Enable error print (debug mode).
     * 
     * @return self
     */
    public function enableDebugMode()
    {
        $this->debugMode = true;
        return $this;
    }

    /**
     * Disable the error print (debug mode)
     * 
     * @return self
     */
    public function disableDebugMode()
    {
        $this->debugMode = false;
        return $this;
    }

    /**
     * Standard mysqli method to run a query.
     * 
     * Returns FALSE on failure. For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries mysqli_query() will return a mysqli_result object. For other successful queries mysqli_query() will return TRUE.
     * 
     * @param string $query Query da eseguire.
     * @return mysqli_result|boolean 
     */
    public function query($query)
    {
        return $this->makeQuery($query);
    }

    /**
     * Standard mysqli method to set the connection charset. 
     * 
     * @param string $charset Codifica caratteri.
     * @return boolean 
     */
    public function set_charset($charset)
    {
        return $this->conn->set_charset($charset);
    }

    /**
     * Standard mysqli method to close the database connection.
     * 
     * @return boolean 
     */
    public function close()
    {
        return $this->conn->close();
    }

    /**
     * Standard mysqli method to escape special characters.
     * 
     * @param mixed $value Value to be escaped
     * @return string
     */
    public function real_escape_string($value)
    {
        return $this->conn->real_escape_string($value);
    }
}

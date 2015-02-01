<?php
/**
* A simple wrapper class for a MySQL PDO database handler 
*
* Selection, deletion, updating, and insertion are supported
* The where part of the statements only supports "=". It does not support "!=", "LIKE" or joins.
*
* @author Aldwin Huynh
*/

class Database {
    // Required database information
    private $db_host_address;
    private $db_username;
    private $db_password;
    private $db_name;
    // PDO Database handler
    private $database = null;

    public function __construct($db_name, $host_address, $username, $password) {
        $this->db_host_address = $host_address;
        $this->db_username     = $username;
        $this->db_password     = $password;
        $this->db_name         = $db_name;
        
        $this->connect();
    }

    /**
     * Connect to database and set $this->database to PDO object
     */
    private function connect() {
        // Catch exception in case PDO throws exception with credentials in message
        try {
            $this->database = new PDO("mysql:host=" . $this->db_host_address . ";dbname=" . $this->db_name, $this->db_username, $this->db_password);
        }catch(Exception $e) {
            throw new Exception("Could not connect to database. ");
        }
        // Allow for exceptions
        $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Perform a query on the database
     * @param   String $sql: The SQL query to perform
     * @return  A PDOStatement object
     */
    public function query($sql) {
        $result = $this->database->query($sql);
        if (!$result) {
            throw new Exception("Query resulted in error: $sql \n" . $this->database->error);
        }

        return $result;
    }

    /**
     * Fetch all rows in the requested table into an associative array
     * @param   String $table_name: The name of the table to fetch
     * @return  An array of arrays of the data of each row
     */
    public function fetchAll($table_name) {
        $sql = "SELECT * FROM $table_name";
        $query_result = $this->query($sql);
        return $query_result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Select all rows from a table that matches requested where values
     * @param  String $table_name : The name of the table to fetch
     * @param  Array $where_values: Mapping of column to values for where part of the SQL statement
     * @return An array of arrays of the data of each row
     *
     */
    public function selectRows($table_name, array $where_values) {
        $sql = Database::createSQLFromWhereStatement("SELECT *", $table_name, $where_values);
        $stmt = $this->prepareAndExec($sql, array_values($where_values));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insert new rows into the requested table in the database, only commits if all inserts are successful
     * @param  String $table_name:   The name of the table to insert into
     * @param  Array $rows:          The rows to insert into the table
     * @return int   $rows_inserted: The number of rows inserted
     */ 
    public function insertRows($table_name, array $rows) {
        $this->database->beginTransaction();
        $rows_inserted = 0;
        // Consider only preparing once to speed up time but would need same column length in every row
        foreach($rows as $row) {
            $rows_inserted += $this->insertRow($table_name, $row);
        }
        // Only committ if all inserts are successful
        $this->database->commit();

        return $rows_inserted;
    }

    /**
     * Insert a row into the requested table in the database
     * @param  String $table_name: The name of the table to insert into
     * @param  Array $row:        The row to insert into the table
     * @return 1 on success
     */ 
    private function insertRow($table_name, array $row) {
        // Comma separated column names
        $col_names= implode(',', array_keys($row));
        // Comma separated place "?" placeholders
        $field_placeholders = rtrim(str_repeat('?,', count($row)), ",");

        $sql = "INSERT INTO  $table_name ($col_names) VALUES ($field_placeholders)";
        $stmt = $this->prepareAndExec($sql, $row);
        return $stmt->rowCount();
    }

    /**
     * Update all rows from a table that matches the inputted where values with set values
     * @param  String $table_name : The name of the table to fetch
     * @param  Array $set_values  : Mapping of column to values for set part of the SQL statement
     * @param  Array $where_values: Mapping of column to values for where part of the SQL statement
     * @return Number of rows updated
     */
    public function updateRows($table_name, array $set_values, array $where_values) {
        
        $set_placeholders   = Database::createWherePlaceHolders($set_values);
        $where_placeholders = Database::createWherePlaceHolders($where_values);

        $sql  = "UPDATE $table_name SET $set_placeholders WHERE $where_placeholders";
        $stmt = $this->prepareAndExec($sql, array_merge($set_values, $where_values));
        // Return number of rows affected
        return $stmt->rowCount();
    }

    /**
     * Delete all rows from a table that matches the inputted where values
     * @param  String $table_name : The name of the table to fetch
     * @param  Array $where_values: Mapping of column to values for where part of the SQL statement
     * @return Number of rows deleted
     */
    public function deleteRows($table_name, array $where_values) {
        $sql  = Database::createSQLFromWhereStatement("Delete", $table_name, $where_values);
        $stmt = $this->prepareAndExec($sql, $where_values);
        // Return number of rows affected
        return $stmt->rowCount();
    }

    /**
     * Create a SQL statement with placeholders for the where part of the statement 
     * @param String $beginning:   The beginning of the SQL statement (something like "insert", "update", "delete", etc.)
     * @param String $table_name : The name of the table to fetch 
     * @param Array $where_values: Mapping of column to values for where part of the SQL statement
     * @return String SQL statement to be prepared
     *
     */
    private static function createSQLFromWhereStatement($beginning, $table_name, array $where_values) {
        $where_placesholders = Database::createWherePlaceHolders($where_values);
        $sql  = $beginning . " FROM $table_name WHERE $where_placesholders";
        return $sql;
    }

    /**
     * Prepare and execute a SQL statement
     * @param String $sql:                The SQL statement to execute
     * @param Array  $placeholder_values: An optional array of values to execute the SQL statement with
     * @return a PDOStatement object used to execute the SQL statement 
     *
     */
    private function prepareAndExec($sql, array $placeholder_values=null) {
        $stmt = $this->database->prepare($sql);
        if(isset($placeholder_values)) {
            $stmt->execute(array_values($placeholder_values));
        } else {
            $stmt->execute();
        }
        return $stmt;
    }

    /**
     * Create string with placeholders for the where values in a SQL statement to be prepared
     * @param  Array $where_values: Mapping of column to values for where part of the SQL statement 
     * @return String of the placeholders for where values
     */
    private static function createWherePlaceHolders(array $where_values) {
        return implode('=? AND ', array_keys($where_values)) . '=?';
    }

    public function __destruct() {
        $database = null;
    }

    public function tests(){
        // Test fetchAll(), insertRows(), updateRows(), and deleteRows()
        print("Before:\n");
        print_r($this->fetchAll("test1"));
        $rows = array();
        $rows[] = array('user' => "t1", 'url' => "t2", 'caption' => 't3', 'id' => '432');
        $rows[] = array('user' => "t5", 'url' => "t6", 'caption' => 't7', 'id' => '234');

        print("\n\nExpected 2 " . $this->insertRows("test1", $rows) . "\n");
        print("Expected 1: " . $this->updateRows("test1",array('url'=>'t6'), array('user' => "t1")) . "\n\nSelect where url = t6:\n");
        print_r($this->selectRows("test1", array('url'=>'t6')));
        print("\nExpected 2: " . $this->deleteRows("test1", array('url'=>'t6')) . "\n");
        print_r($this->fetchAll("test1"));
    }
}
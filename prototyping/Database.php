<?php

//TODO: Bind parameter and use prepare method instead of manually doing it
//TODO: Object mapping using SQLI methods 
//TODO: Rewrite with PDO
class Database {
    //Default database information
    private $db_host_address = '127.0.0.1';
    private $db_username     = 'root';
    private $db_password     = 'password';
    private $db_name         = 'cache';
    //MYSQLI object
    private $database = null;

    public function __construct($db_name = null, $host_address = null, $username = null, $password = null) {
        //Change database information if given, otherwise use default info
        if(!empty($host_address) && !empty($username) && !empty($password)) {
            $this->db_host_address = $host_address;
            $this->db_username     = $username;
            $this->db_password     = $password;
        }

        if(!empty($db_name)) {
            $this->db_name = $db_name;
        }

        $this->connect();
    }

    /**
     * Connect to database and set $this->database to mysqli object
     */
    private function connect() {
        $this->database = new mysqli($this->db_host_address, $this->db_username, $this->db_password, $this->db_name);

        if(!$this->database || mysqli_connect_errno()) {
              throw new Exception("Error connecting to database. \n" . mysql_error());
        }
    }

    /**
     * Perform a query on the database
     * @param   String $sql: The SQL query to perform
     * @return  A MYSQLI result object
     */
    public function query($sql) {
        //Check connection
        if ($this->database->error) {
            throw new Exception("Error connecting to database. \n" . $this->database->error);
        }


        $result = $this->database->query($sql);
        if (!$result) {
            throw new Exception("Query resulted in error: " . $sql . " \n" . $this->database->error);
        }

        return $result;
    }

    /**
     * Fetch all rows in the requested table
     * @param   String $table_name: The name of the table to fetch
     * @return  An array of arrays of the data of each row
     */
    public function fetchAll($table_name) {
        $sql = "SELECT * FROM " . $table_name;
        $query_result = $this->query($sql);
        return $this->fetchAllResults($query_result);
    }

    /**
     * Takes a MYSQLI result object and turns it into an array of arrays representing the data of the result
     * @param   MYSQLI Result $query_result: The result from a MYSQLI query
     * @return  An array of arrays of the data of each row
     */
    private function fetchAllResults($query_result) {
        $combined_results = array();
        while($row = $query_result->fetch_assoc()) {
            $combined_results[] = $row;
        }
        return $combined_results;
    }
    
    /**
     * Insert new rows into a given table in the database
     * @param  String $table_name: The name of the table to insert into
     * @param  Array $col_names:   An array of all the column names of that table
     * @param  Array $rows:        The rows to insert into the table
     * @return 1 on success 
     */ 
    public function insertRows($table_name, array $col_names, array $rows) {
        //TODO:support for different columnt types, only supports string ATM
        //Turn $col_names into string used for SQL statement
        $col_names_comma_separated = rtrim(implode(',', $col_names), ',');
        
        //Write the SQL query for the insertion
        $sql = "INSERT INTO " . $table_name . " (" . $col_names_comma_separated . ") VALUES ";
        foreach($rows as $row) {
            $sql .= "(" . $this->convertRowForInsertion($col_names, $row) . "), ";
        }
        //Remove ending comma
        $sql = rtrim($sql, ', ') . ";";

        return $this->query($sql);
    }

    /**
     * Take a given row and turn it into a string for SQL statement
     * @param  Array $col_names:   An array of all the column names of that table
     * @param  Array $row:         The rows to insert into the table
     * @return string representation of $row
     */
    private function convertRowForInsertion(array $col_names, $row) {
        $row_comma_separated = "";
        foreach($col_names as $col_name) {
            if(empty($row[$col_name])) {
                throw new Exception ("Column names given don't match with row information in Insertion. ");
            }

            $row_comma_separated .= "'" . $row[$col_name] . "', ";
        }
        //Remove ending comma
        return rtrim($row_comma_separated, ', ');
    }

    public function __destruct() {
        //Close connection to database
        if($this->database) {
            $this->database->close();
        }
    }

}

/*TESTS
$db123 = new Database();
echo "lul";
print_r($db123->fetchAll("Prototype1"));


$col_names = array('user', 'url', 'caption', 'id');
$rows = array();
$rows[] = array('user' => "phptest123", 'url' => "te", 'caption' => 'dra', 'id' => '1234');

print($db123->insertRows("Prototype1", $col_names, $rows));

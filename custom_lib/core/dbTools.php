<?php 

/**
 * Attempts to connect to a database, and if successful stores the connection in a variable.
 * @param mysqli $retVar The mysqli object to set, if any.
 * @param string $dbHost The hostname of the database.
 * @param string $user The account username.
 * @param string $pass The account password.
 * @param string $schema (optional) The default schema to set for this connection.
 * @param string $errorDescriptor (optional) The word to use in connection errors to describe this DB server.
 * @return bool True on connection success, false otherwise. 
 */
function tryConnect(&$retVar, $dbHost, $user, $pass, $schema="", $errorDescriptor ="the")
{
    $retVar = @new mysqli($dbHost,$user,$pass,$schema);
    
    if($retVar == null || mysqli_connect_errno() > 0) {
        error_log("DBTools - Can't connect to $errorDescriptor MySQL server $dbHost. Error msg: ".mysqli_connect_error());
        error_log("DBTools - $user, $pass, $dbHost");
        $retVar = null;
        return false;
    } else return true;
}

class DBHandler
{
    /**
     * @var mysqli the database object
     */
    private $mysqli;
        
    /**
     * @var string DB host server
     */
    private $host="localhost";   
    
    
    public $total_rows=0;
    
    public $schema;
    
    private $_mysqli_last = null; // pointer to the last mysql object used to run a query - holds error message.
    
    /**
     * Construct a DBTool object
     * 
     * @param boolean $singleServerMode  Select whether a writable DB connection should also connect to a different Read Only server
     */
    public function __construct($schema)
    {
            //Constructor      
            $this->schema = $schema;
    }
    
    /**
     *
     * @param boolean $writeable if false will allow read only access to database
     * @param string $account choose a different account to access the DB (autoencoder,livestream,dvbcapture,stats,austar,dropbox,etc...)
     * @param string $host the database host to use, defaults to 10.2.1.223 which is macsvdb1.switch.internal
     *
     * @throws Exception on database connection fail
     */
    function connect($account="root",$host="") {	
        if($account=="root") {
            $user = "arw49555_b5";
            $pass = "f1shb0ard";
            $schema = $this->schema;
        }
            
        if(strlen($this->host)>0) {

            if(tryConnect($this->mysqli, $host, $user, $pass, $schema, "specific")) 
                return;
            else
                throw new Exception("Can't connect to DB Server {$this->host}. Error msg: ". mysqli_connect_error());
        }
        else
            throw new Exception("No DB host specified");
    
        return;
    }
    
    /**
     * A generic SQL query wrapper function to centralise logging.
     * @param string $sql the SQL query to be run. Please make sure this query has been sanitised!
     * @param bool $readOnly Whether to use the read-only DB connection handle.
     * @return object A mysqli results object.
     */
    function doQuery($sql) {
        
        $result = $this->mysqli->query($sql);
        $this->_mysqli_last=$this->mysqli;
        
        return $result;
    }
    
    
    /**
     * Runs a query and returns a multiples row as an multidimensional associative array.
     *
     * @param string $sql the SQL query to be run. Please make sure this query has been sanitised!
     * @param Booleam $calcRows Flag to determine whether the total rows should be calculate for queries with a SQL_CAL_FOUND_ROWS and LIMIT clause
     * @return array associative array containing the results
     *
     */
    function getMultiDimensionalArray($sql,$calcRows=false) {		
        $data = array();
        if($result = $this->doQuery($sql))
        {

                $data = self::convertResultsToHashtable($result);
                if($calcRows)
                {
                        $rows = $this->doQuery("SELECT FOUND_ROWS() AS 'found_rows';");
                        $rows = $rows->fetch_assoc();
                        $this->total_rows = $rows['found_rows'];
                }
                $result->close();
        }
        else
                throw( new Exception("Query failed! $sql ".$this->_mysqli_last->error));

        return $data;
    }
    
    function getResults($sql, $calcRows=false) {
        if($result = $this->doQuery($sql)) {
            if($calcRows) {
                $rows = $this->doQuery("SELECT FOUND_ROWS() AS 'found_rows';",true);
                $rows = $rows->fetch_assoc();
                $this->total_rows = $rows['found_rows'];
            }

            return $result;
        }
        else
            throw( new Exception("Query failed! $sql ".$this->_mysqli_last->error));
    }
    
    /**
     * Closes the mysqli connection
     */
    function close() {
        $this->mysqli->close();
    }
        
    /**
     * Takes a mysqli results object and iterates over it to convert it into a hash table
     * @param mysqli_result $results
     * @return array array containing results
     */
    function convertResultsToHashtable($results)  {
        $hash = array();
        $count = $results->num_rows;
        for($i=0;$i<$count;$i++)  {
             $hash[] = $results->fetch_assoc();
		}
        return $hash;
    }
    
    /**
     * Runs a query and returns a single row as an associative array.
     *
     * @param string $sql the SQL query to be run. Please make sure this query has been sanitised!
     * @return array associative array containing the results
     *
     * @throws Exception if there are no results returned
     * @throws Exception if there are more than 1 rows returned
     * @throws Exception if there is a MySQL error
     */
    function getSingleRowAssoc($sql)
    {
            if($result = $this->doQuery($sql, true))
            {
                    if($result->num_rows == 0)
                    {
                            throw new Exception(sprintf("Failed! Couldn't find result. '%s'",$sql));

                    }
                    else if($result->num_rows > 1)
                    {
                            throw new Exception(printf("Failed! Duplicate results(%d) found for '%s'",$result->num_rows,$sql));
                    }
                    $row = $result->fetch_assoc();
                    $result->close();
                    return $row;
            }
            else
            {
                    throw( new Exception("Query failed! $sql ".$this->_mysqli_last->error));
            }
            return null;
    }
    
    /*
     * Function to expose the MySQLi escape_string() function
     */
    function escape($str)
    {
            return $this->mysqli->real_escape_string($str);	
    }
    
    /**
     * Runs a query which should be an UPDATE query, as it will return the MySQL affected rows.
     *
     * @param string $sql the SQL query to be run. Please make sure this query has been sanitised!
     * @return integer the MySQL affected rows
     *
     * @throws Exception a string containing the mysql error
     */
    function update($sql)
    {

        if(stripos($sql,"UPDATE")===false)
        {
            die("Not an update query: ".$sql."  ".$this->mysqli->error);
        }

        if(!$this->doQuery($sql))
        {
                throw( new Exception("Query failed! $sql ".$this->_mysqli_last->error));
        }
        return $this->mysqli->affected_rows;
    }
    
    /**
     * Runs a query which should be an INSERT query, as it will return the MySQL insert ID.
     *
     * @param string $sql the SQL query to be run. Please make sure this query has been sanitised!
     * @return integer the MySQL insert ID
     *
     * @throws Exception a string containing the mysql error
     */
    function insert($sql)
    {

        if(stripos($sql,"INSERT")===false && stripos($sql,"REPLACE")===false)
        {
            die("Not an insert or replace query: ".$sql."  ".$this->mysqli->error);
        }

            $err = $this->doQuery($sql);
            if(!$err)
            {
                    throw( new Exception("Query failed! $sql ".$this->_mysqli_last->error));
            }

            return $this->_mysqli_last->insert_id;
    }
    
    /**
     * Runs a query and returns a single row as an enumerated array.
     *
     * @param string $sql the SQL query to be run. Please make sure this query has been sanitised!
     * @return array enumerated array containing the results
     *
     * @throws Exception if there are no results returned
     * @throws Exception if there are more than 1 rows returned
     * @throws Exception if there is a MySQL error
     */
    function getSingleRow($sql)
    {
            if($result = $this->doQuery($sql, true))
            {
                    if($result->num_rows == 0)
                    {
                            throw new Exception(sprintf("Failed! Couldn't find result. '%s'",$sql));

                    }
                    else if($result->num_rows > 1)
                    {
                            throw new Exception(printf("Failed! Duplicate results(%d) found for '%s'",$result->num_rows,$sql));
                    }
                    $row = $result->fetch_row();
                    $result->close();
                    return $row;
            }
            else
            {
                    throw( new Exception("Query failed! $sql ".$this->_mysqli_last->error));
            }
            return null;
    }

    
    /**
     * Runs a query and returns a single value. Since this function calls getSingleRow
     * it will throw all the usual exceptions.
     *
     * @param string $sql the SQL query to be run. Please make sure this query has been sanitised!
     * @return mixed    the value of the first field of the row returned by the query.
     *
     * @throws Exception if there are no results returned
     * @throws Exception if there are more than 1 rows returned
     * @throws Exception if there is a MySQL error
     */
    function getSingleValue($sql)
    {
            $row = $this->getSingleRow($sql);
            //error_log("wtf: $row[0]");
            return $row[0];
    }
    
    /**
     * Runs a query and return an array of results, single column query returned as an array
     *
     * @param string $sql the SQL query to be run. Please make sure this query has been sanitised!
     * @return array enumerated array containing the single column results
     *
     */
	function getSingleValueArray($sql)
	{
		
		if($result = $this->doQuery($sql, true))
		{
			if($result->num_rows >= 0)
			{
				$vals = Array(); 
				for($i=0;$i< $result->num_rows;$i++)
				{
					$row=$result->fetch_array();
					$vals[] = $row[0];	
				}
				$result->close();
				
			}
			else
				return null;
		}
		else 
			return Array();
			
		return $vals;	
	}
}
?>
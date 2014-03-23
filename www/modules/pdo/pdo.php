<?

# Class to manage connections.
class Database
{
	#-------------------------#
	# Variables and Constants #
	#-------------------------#
	
	// Hold the database connection.
	private $Database;
	
	// Errors that may happen
	private $Errors;
	
	// The connection status: Alive, Died, Duration, NumOfQueries.
	private $ConnectionStatus;
	private $QueriesPerformed;
	
	// Return for $QueriesPerformed
	public function getNumOfQueries() { return $this->ConnectionStatus['NumOfQueries']; }
	public function getConnectionDuration() { return $this->ConnectionStatus['Duration']; }
	public function getQueries() { return $this->QueriesPerformed; }
	
	// Message Constants
	const ERROR_CANNOT_CONNECT_TO_DATABASE = '[Code: 1] Cannot connect to the database.';
	const ERROR_SQL_SYNAX_INCORRECT = '[Code: 2] SQL synax error.';
	const ERROR_INCORRECT_KEY_PARSE = '[Code: 3] Invalid KEY data.';
	const ERROR_DATA_ENTRY_ISSUE = '[Code: 4] There was an error inserting new data.';
	
	#----------------------#
	# Connection and Setup #
	#----------------------#
	
	// Connect to the database, requires a key login.
	public function __construct()
    {
    	global $db_config;

    	if (!is_array($db_config) || !isset($db_config['host']) || !isset($db_config['dbname']) || !isset($db_config['username']) || !isset($db_config['password']))
    	{
    		trigger_error("[new Database() > new PDO()] db_config missing information, @db_config = ". print_r($db_config, true), E_USER_ERROR);
    		return false;
    	}

    	// Try connect
        try 
        {
            $db_config['port'] = isset($db_config['port']) ? $db_config['port'] : 3306;

            // Successfull connection sets database variable to hold connection
            $NewDB = new PDO(
                'mysql:host=' . trim($db_config['host']) . ';port=' . $db_config['port'] . ';dbname=' . trim($db_config['dbname']) . ';charset='. $db_config['charset'],
            	trim($db_config['username']), 
            	trim($db_config['password']), 
				array(
					PDO::ATTR_PERSISTENT => false
				));

            if ($NewDB)
            {
            	$this->Database = $NewDB;

            	// Configuration for prepared statements
	            $this->Database->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	            $this->Database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	            $this->Database->setAttribute(PDO::ATTR_TIMEOUT, 30);

	            // Set the time the connection became alive.
	            $this->ConnectionStatus['Alive'] = microtime(true);

	            return true;
            }
            else
            {
            	trigger_error("[new Database() > new PDO()] NewDB = false, @params = ". print_r($params, true), E_USER_ERROR);
            	return false;
            }
        } 

        // Failed connection attempt
        catch (PDOException $e) 
        {
        	trigger_error("[new Database() > new PDO() > PDOException] (". $e->getMessage() .") @params = ". print_r($params, true), E_USER_ERROR);

            // If debug is set, we can spit out the exact error to help developers track the issue
            if (isset($_GET['debug'])) 
            {
                // Set error variable to the PDO Exception error
                echo $e->getMessage();
                return false;
            } 
            else 
            {
                // Set error to a generic error we can print to our users
                $this->Errors = self::ERROR_CANNOT_CONNECT_TO_DATABASE;
                return false;
            }
            die;
        }
	}
	
	// Deconstruction.
	public function __destruct()
    {
		$this->Disconnect();
	}
	
	// Disconnect from database.
	public function Disconnect()
    {
        if (!$this->Database) {
            return;
        }

		// Set the time of when the connection died
		$this->ConnectionStatus['Died'] = microtime(true);
		
		// Set the duration it was alive in seconds.
		$this->ConnectionStatus['Duration'] = ($this->ConnectionStatus['Died'] - $this->ConnectionStatus['Alive']);
		
		// Sort just to make it look nice
		ksort($this->ConnectionStatus);
		
		// Close the database
		$this->Database = NULL;
	}
	
	// Returns the errors.
	public function getErrors()
    {
		return $this->Errors;	
	}

	public function hasConnection()
	{
		$HasConnection = isset($this->Database) ? true : false;
		if (!$HasConnection) { $this->Disconnect(); }
		return $HasConnection;
	}
	
	private function getType($Type)
	{
		$Types = array
		(
			"STR" => $this->Database->PARAM_STR
		);
		
		return $Types[$Type]?$Types[$Type]:$Type;
		
	}
	
	#-----------------#
	# Main query call #
	#-----------------#
	
	// Method to fetch data via a raw query
	private function Query($sql, $type, $all = false, $params = NULL)
    {
		// Add Debugging statuses.
		if (isset($this->ConnectionStatus['NumOfQueries'])) { $this->ConnectionStatus['NumOfQueries'] = $this->ConnectionStatus['NumOfQueries'] + 1; }
		$this->QueriesPerformed[microtime(true)][] = $sql;

		try {	
			// Run Query
			$Query = $this->Database->prepare($sql);
			if($params)
			{
				//print_r($params);
				foreach($params as $id => $param)
				{
					if(is_array($param))
					{
						$param[1] = isset($param[1]) ? $param[1] : NULL;
						$param[2] = isset($param[2]) ? $param[2] : NULL;
						$Query->bindParam($id, $param[0], $param[1], $param[2]);
					}else
						$Query->bindParam($id, $param);
				}
			
			}

			$Query->execute();
			// Error Checking
			$Errors = $Query->errorInfo();
			if ($Errors[2])
			{
				trigger_error("[func->Query() > SYNTAX ERROR] sql = ". $sql, E_USER_ERROR);

				// If we are debugging, print errors more easier to read.	
				if (isset($_GET['debug'])) {
					$this->Errors = array($Errors[2], $sql);
					return false;
				} else {
					$this->Errors = self::ERROR_SQL_SYNAX_INCORRECT; 
					return false;
				}
			}
			else
			{
				
				// Return based on type
				if ($type == 'get') {
					
					// Get results
					if ($all)
						$Data = $Query->fetchAll(PDO::FETCH_ASSOC);
					else
						$Data = $Query->fetch(PDO::FETCH_ASSOC);
					
					// If successful, return the insertion id.
					return array(
						"rows" => $Query->rowCount(),
						"data" => $Data
					);
				}
				else if ($type == 'insert')
				{
					// return inserted id
					return array("ID" => $this->Database->lastInsertId());
				}
				else if ($type == 'raw')
				{
					return $Query->fetchAll(PDO::FETCH_ASSOC);
				}
				else if ($type == 'IU') // insert on dup key update 
				{
					return array("ID" => $this->Database->lastInsertId()); //$Query->fetchAll(PDO::FETCH_ASSOC);
				}
			}
		} 
		// If an error caught
		catch(PDOException $e)
		{
			trigger_error("[func->Query() > PDOException] (". $e->getMessage() .") sql = ". $sql, E_USER_ERROR);

			// If debug is set, we can spit out the exact error to help developers track the issue
			if (isset($_GET['debug'])) { 
			
				// Set error variable to the PDO Exception error
				echo $e->getMessage();
				
				die;
			
			} else { 
			
				// Set error to a generic error we can print to our users
				$this->Errors = self::ERROR_DATA_ENTRY_ISSUE; 
				return false;
			}
			
		}
	}
	
	#---------#
	# Methods #
	#---------#
	
	/*
		Method to fetch data, returns an array: [Rows]Numer of rows, [Data]The data.
		If there is an error, returns false.
		Default: "select * (where = NULL) order by AUTO asc limit(0,10) (fetch 1)"
		
		Example:
		GetData('table', 
				array('column1', 'column2', 'column3'), 
				true,
				array('column1' => 'data'),
				'AND'
				column,
				COLUMN,
				DESC,
				array(Start, Length);
				
		Limit can be set to false to ignore.
	*/
	public function Get($Table, $Fields = "*", $All = FALSE, $Where = NULL, $WhereOperator = NULL, $GroupBy = NULL, $Order = NULL, $Direction = NULL, $Limit = NULL, $NoFilter = false, $params = null) {
				
		// We query by constructing an SQL query string.
		$sql = 'SELECT ';
		
		// Get the columns from the field array.
		if (is_array($Fields)) { $sql .= implode(', ', $Fields) .' '; } else { $sql .= $Fields .' '; } 
		
		// Append from table
		$sql .= 'FROM '. $Table .' ';
		
		// Append where statement (if one exists)
		if ($WhereOperator == NULL) { $WhereOperator = ' AND '; }
		if ($Where) { $sql .= 'WHERE '. implode(' '. $WhereOperator .' ', $Where) .' '; }
		
		// Append groupby statement (if one exists)
		if ($GroupBy) { $sql .= 'GROUP BY '. $GroupBy .' '; }
		
		// Append order (if one exists)
		if ($Order && !$NoFilter) { $sql .= 'ORDER BY '. $Order .' '. $Direction .' '; }

		// Append limit (if one exists)
		if ($Limit) { $sql .= 'LIMIT '. $Limit[0] .','. $Limit[1] .''; }

		// Query
		return $this->Query($sql, 'get', $All, $params);
		
	}
					//GetData($Table, $Fields, $All, $Where = NULL, $Order = "AUTO", $Direction = "ASC", $Limit = NULL, $NoFilter = false)
	// Support for old PDO
	public function GetData($Table, $Fields, $All, $Where = NULL, $Order = "AUTO", $Direction = "ASC", $Limit = NULL, $NoFilter = false, $debug = false, $params = null) {
		if (is_array($Fields))
			$FieldsDisplay = implode("|", $Fields);
		else
			$FieldsDisplay = $Fields;
		
		$Start = microtime();
		
		// Create fields into comma string if an array
		if (is_array($Fields))
			$field_values = implode(",", $Fields);
		else
			$field_values = $Fields;
			
		// Set WHERE clause
		if (is_array($Where))
			$where_conditions = implode(" AND ", $Where);
		else
			$where_conditions = $Where;
		
		// If WHERE conditions set, create string condition	
		if (isset($where_conditions))
			$where_conditions = "WHERE ". $where_conditions;
			
		// Set Limit
		$limit_conditions = NULL;
		if (isset($Limit))
			$limit_conditions = "LIMIT ". implode(",", $Limit);
		
		$OrderBy = "";
		if(!$NoFilter)
			$OrderBy = "ORDER BY ". $Order ." ". $Direction;
		
		if($debug)
			print_r("SELECT ". $field_values ." FROM ". $Table ." ". $where_conditions ." ". $OrderBy ." ". $limit_conditions ."");
		
		return $this->Query("SELECT ". $field_values ." FROM ". $Table ." ". $where_conditions ." ". $OrderBy ." ". $limit_conditions ."", 'get', $All, $params);
	}
	
	// Allows free sql input (restricted to hardcoded input only)
	public function SQL($sql, $params = null) {
		return $this->Query($sql, 'raw', null, $params);
	}
	
	// Inert on duplicate key update statements
	public function IUSQL($sql, $params = null) {
		return $this->Query($sql, 'IU', null, $params);
	}
	
	public function RawQuery($sql, $params = null) {
		return $this->Query($sql, 'raw', null, $params);
	}
	
	// Method to insert data into a table.
	public function Insert($Table, $Data, $params = null) {
		
		// We query by constructing an SQL query string.
		$sql = 'INSERT INTO ';
		
		// Append table name.
		$sql .= $Table .' ';
		
		// If bind params set
   		if ($params)
   		{
	   		// implode keys of $array...
			$sql .= "(".implode(", ", array_keys($Data)).") ";
			
			// implode values of $array...
			$sql .= "VALUES (".implode(", ", $Data).") ";		
		}
		else
		{
			// implode keys of $array...
			$sql .= "(`".implode("`, `", array_keys($Data))."`) ";
			
			// implode values of $array...
			$sql .= "VALUES ('".implode("', '", $Data)."') ";	
		}

		
		
		// Query
		return $this->Query($sql, 'insert', null, $params);
	}
	
	// Method to update the data into a table.
	public function Update($Table, $Data, $Where, $params = null) {
		
		// We query by constructing an SQL query string.
		$sql = 'UPDATE ';
		
		// Append table name.
		$sql .= $Table .' ';
   
		// Append data.
		$sql .= 'SET '. implode(",", $Data) .' ';
		
		// Append where clause.
		$sql .= 'WHERE '. implode(" AND ", $Where);
		
		// Query
		return $this->Query($sql, 'update', null, $params);
	}

	// Method to remove data from a table.
	public function Remove($Table, $Where, $params = null) {
		
		// We query by constructing an SQL query string.
		$sql = 'DELETE FROM ';
		
		// Append table name.
		$sql .= $Table .' ';
		
		// Append where clause.
		$sql .= 'WHERE '. implode(" AND ", $Where);
		
		// Query
		return $this->Query($sql, 'remove', null, $params);
	}

	
}	
?>

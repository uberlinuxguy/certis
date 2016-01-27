<?php
/**
* This is a  class for working with the database objects from PDO.
*
* @package System
* @author Edwin & Jason <uberlinuxguy@tulg.org>
*/

/* TODO: DB Class needs some double checking and error checking.  This class went though
 * several iterations as I was developing it, so there may be un-needed stuff in here.
 * Also need to test this against some other Database Engines to make sure the SQL in here
 * is cross-engine compliant.
 */

class DB extends Certis{

    protected $_where = NULL;

    protected $_where_clause = NULL;

    protected $_join_clause = NULL;

    protected $_fields = "*";

    protected $_groupby = NULL;

    protected $_orderby = NULL;

    protected $_limit = NULL;

    protected $_offset = NULL;

    protected $_join = NULL;

    protected $_dbr = NULL;

    protected $_dbw = NULL;

    protected $_metadata = array();

    protected $_dbuser = NULL;

    protected $_dbpass = NULL;

    protected $_dbname = NULL;

    protected $_dbreadhost = NULL;

    protected $_dbwritehost = NULL;

    protected $_dbtype = NULL;

    protected $_dbtrans = TRUE;

    /**
    * Constructor.
    *
    * @access public
    * @return void
    */
    public function __construct() {

        if(isset($_ENV['describeTable'])) {
            if(!is_array($_ENV['describeTable'])) {
                $_ENV['describeTable'] = array();
            }
        }
        else {
            $_ENV['describeTable'] = array();
        }

    }

    /**
    * Magic method for setting up the different query parameters
    *
    * @access   private
    * @param    string  $funcname           Name of the function called
    * @param    string  $params             array of the parameters passed in
    * @return   mixed
    */
    public function __call($funcname, $params = array()) {

        if(property_exists($this, '_' . $funcname)){
        	//if(!is_object($params[0])){ 
        		API::DEBUG("[DB::__call()] Setting $funcname ",7);
        	//}
            $prop_name = '_' . $funcname;
            $this->{$prop_name} = $params[0];

        }

    }

	/**
	* Creates a new PDO object and sets the corresponding
    * handle on this object
	*
    * @access   private
	* @param    array       $params     Array of data for connecting
	* @param    string      $type       Type of connection (read|write|rw)
	* @return   void
	*/
	private function _connect($type = 'write') {

        if($type == 'rw') {
            $type = 'write';
            $this->_connect('read');
        }

        if($type == 'write') {
            if($this->_dbw != NULL) {
                return;
            }
        } else {
            if($this->_dbr != NULL) {
                return;
            }
        }
        if(isset(self::$config->db)) {
            $this->set_config(self::$config->db);
        }

        // connect to the right host.
        $host = ($type == 'write') ? $this->_dbwritehost : $this->_dbreadhost;

        // set up the DSN
		$dsn = $this->_dbtype . ':host=' . $host . ';dbname=' . $this->_dbname;
		try {
			$pdo = new PDO($dsn,$this->_dbuser,$this->_dbpass);
			$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
			$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			error_log('[System] DB::connect => Failed to connect to '.$host.': '.$e->getMessage());
			exit;
		}

        // set the appropriate db handle.
        if($type == 'write') {
            $this->_dbw = $pdo;
        } else {
            $this->_dbr = $pdo;
        }
	}



    /**
    * Set up the db config options
    *
    * @param    array       $config         the config options
    * @return   void
    */
    public function set_config($config) {
        $this->_dbuser = $config->dbuser;
        $this->_dbpass = $config->dbpass;
        $this->_dbname = $config->dbname;
        $this->_dbtype = $config->dbtype;
        $this->_dbwritehost = $config->writehost;
        $this->_dbreadhost = $config->readhost;
        $this->_dbtrans = (isset($config->dbtrans)) ? $config->dbtrans : $this->_dbtrans;
    }

	/**
	* Insert a new row into the specified table
	*
	* @param string $table Table to insert row into
	* @param array $data Data to be insert into table
	* @return integer
	*/
	public function insertRow($table,$data) {

        $this->_connect('write');
		$tokens = array();
		# Create the tokens array
		foreach ($data as $field => $value) {
			if (preg_match('/^=/',$value)) {
				$tokens[] = substr($value,1);
			} else {
				$tokens[] = ':'.$field;
			}
		}

		$sql = 'INSERT INTO '.$table.' ('.join(',',array_keys($data)).') VALUES('.join(',',$tokens).')';

		# Start a transaction
		if ($this->_dbtrans === TRUE) {
			$this->_dbw->beginTransaction();
		}

		try {
			$stmt = $this->_dbw->prepare($sql);
			foreach ($data as $field => $value) {
				if (preg_match('/^=/',$value)) {
					continue;
				}
				$stmt->bindValue(':'.$field,$value);
			}
			if ($stmt->execute()) {
				$id = $this->_dbw->lastInsertId();
				if ($this->_dbtrans === TRUE) {
					$this->_dbw->commit();
				}
				if (is_numeric($id)) {
					return $id;
				}
			}
		} catch (PDOException $e) {
			if ($this->_dbtrans === TRUE) {
				$this->_dbw->rollBack();
			}
			error_log('[System] Failed Generated Query: '.$sql);
			throw new Exception($e->getMessage());
		}
	}

	/**
	* Update row(s) in the specified table
	*
	* @param string $table Table to update row in
	* @param array $data Data to be updated in the row(s)
	*/
	public function updateRow($table,$data) {

        $this->_connect('write');

		# Create the tokens array
		foreach ($data as $field => $value) {
			$utokens[] = $field.' = :'.$field;
		}
		$tmp_where = "WHERE ";
		# Build our WHERE clause for the query

		if (!is_null($this->_where) && !is_array($this->_where)) {
			$tmp_where .= $this->_where.' ';
		}

		// if _where_clause is an object, then we should let the WhereClause
		// class process the where clause.
		if(is_object($this->_where_clause)) {
			$tmp_where .= $this->_where_clause->build_clause();
		}
		$cond = "";
		if($tmp_where != "WHERE ") {
			$cond .= $tmp_where;
		}


		$sql = 'UPDATE '.$table.' SET '.join(',',$utokens).' '.$cond;

		# Start a transaction
		if ($this->_dbtrans === TRUE) {
			$this->_dbw->beginTransaction();
		}

		try {
			$stmt = $this->_dbw->prepare($sql);
			foreach ($data as $field => $value) {
				$stmt->bindValue(':'.$field,$value);
			}

			if(is_object($this->_where_clause)) {
					$this->_where_clause->bind_values($stmt);
			}

			if ($stmt->execute()) {
				if ($this->_dbtrans === TRUE) {
					$this->_dbw->commit();
				}
				return TRUE;
			}
		} catch (PDOException $e) {
			if ($this->_dbtrans === TRUE) {
				$this->_dbw->rollBack();
			}
			error_log('[System] Failed Generated Query: '.$sql);
			throw new Exception($e->getMessage());
		}
		return FALSE;
	}
	/**
	* Delete a row from the database
	*
	* @param string $table Table to delete row(s) from
    *
	*/
	public function deleteRow($table, $empty_table) {
        $this->_connect('write');

		$sql = "DELETE FROM $table ";
		if($empty_table != true) {
			$tmp_where = "WHERE ";
			// if _where_clause is an object, then we should let the WhereClause
			// 	class process the where clause.
			if(is_object($this->_where_clause)) {
				$tmp_where .= $this->_where_clause->build_clause();
			}
			$sql .= $tmp_where;

		}

		if ($this->_dbtrans === TRUE) {
			# Begin Transaction
			$this->_dbw->beginTransaction();
		}

		try {
			$stmt = $this->_dbw->prepare($sql);

			if(is_object($this->_where_clause)) {
					$this->_where_clause->bind_values($stmt);
			}

			if ($stmt->execute()) {
				if ($this->_dbtrans === TRUE) {
					$this->_dbw->commit();
				}
			}
		} catch (PDOException $e) {
			if ($this->_dbtrans === TRUE) {
				$this->_dbw->rollBack();
			}
			error_log("[System::DB] Error on query $sql");
			throw new Exception($e->getMessage());
		}
	}


	/**
	* Generate and execute a SELECT query using the given information.
	*
	* @param string $table Table to retrieve row(s) from
    * @return   mixed
	*/
	public function getWhere($table=NULL, $just_count = FALSE) {

        $this->_connect('read');
        if(!isset($table) || empty($table)) {
        	$table = $this->_table;
        }
        if($just_count === TRUE) {
        		$sql = 'SELECT count(*) as count FROM ' . $table . ' ';
        } else {
			$sql = 'SELECT ' . $this->_fields . ' FROM '.$table.' ';
        }
	

		if(is_object($this->_join_clause)) {

			$sql .= $this->_join_clause->build_clause();
		}
        
        // we still want to allow hand coded where clauses,
        // so let's do this the right way...
        $tmp_where = " WHERE ";
        if(!empty($this->_where)) {
        	if(is_array($this->_where)) {
        		throw new Exception("Use of array based where clauses no longer supported.");
        	} else {
        		$tmp_where .= $this->_where;
        	}
        }

        // now for the stuff from our WhereClause class
        if(is_object($this->_where_clause)) {

        	$tmp_where .= $this->_where_clause->build_clause();
        }
		if($tmp_where != " WHERE ") {
			$sql .= $tmp_where;
		}
		unset($tmp_where);

		# Check for grouping
		if (@count($this->_groupby) > 0) {
			$sql .= 'GROUP BY '.join(',',$this->_groupby).' ';
		}

		# Check for sort order
		if (@count($this->_orderby) > 0) {
			foreach ($this->_orderby as $field => $direction) {
				$orders[] = $field.' '.$direction;
			}
			$sql .= 'ORDER BY '.join(',',$orders).' ';
		}

		# Check for limit and offset
		if (is_numeric($this->_limit)) {
			$sql .= 'LIMIT '.$this->_limit;
			if (is_numeric($this->_offset)) {
				$sql .= ' OFFSET '.$this->_offset;
			}
		}
		try {
			API::DEBUG("[DB::getWhere] $sql",9);
			$stmt = $this->_dbr->prepare($sql);


			// use the WhereClause class to bind the values.
			if(is_object($this->_where_clause)) {
				$this->_where_clause->bind_values($stmt);
			}

			// use the JoinCluase class to bind the values
            if(is_object($this->_join_clause)) {
            	$this->_join_clause->bind_values($stmt);

            }
			//API::DEBUG("[DB::getWhere()] sql = $sql",1);
			if ($stmt->execute()) {
				if ($stmt->columnCount() == 1) {
					while ($col = $stmt->fetchColumn()) {
						$rows[] = $col;
					}
				} else {
					$rows = $stmt->fetchAll();
				}

/*
				if (@count($rows) == 1) {
					return $rows[0];
				} elseif (@count($rows) == 0 ){
					return NULL;
				}*/
				//error_log(var_export($rows,true));
                if(isset($rows)) {
    				return $rows;
                } else {
                	API::DEBUG("[DB::getWhere()] Returning NULL as a result");
                    return NULL;
                }
			}
		} catch (PDOException $e) {
			error_log('[System] Failed Generated Query: '.$sql);
			throw new Exception($e->getMessage());
		}
	}

	/**
	* Prepare and Execute the given query, binding any variables that are passed
	* This method is just here to allow us to cut down on some code in other places
	*
	* @param string $sql SQL query to prepare/execute
	* @param array $data Variables that need to be bound (Format: token => value)
	* @return mixed
	*/
	public function query($sql,$data = NULL) {

        // write because this COULD be a write DB operation.
        $this->_connect('write');

		if ($this->_dbtrans === TRUE) {
			# Begin Transaction
			$this->_dbw->beginTransaction();
		}

		try {
			$stmt = $this->_dbw->prepare($sql);
			if (is_array($data)) {
				if (@count($data) > 0) {
					foreach ($data as $token => $value) {
						$stmt->bindValue(':'.$token,$value);
					}
				}
			}
			if ($stmt->execute()) {
				if ($this->_dbtrans === TRUE) {
					$this->_dbw->commit();
				}
				if ($stmt->columnCount() == 1) {
					while ($col = $stmt->fetchColumn()) {
						$rows[] = $col;
					}
				} else {
					$rows = $stmt->fetchAll();
				}
				if (@count($rows) == 1) {
					return $rows[0];
				}
				return $rows;
			}
		} catch (PDOException $e) {
			if ($this->_dbtrans === TRUE) {
				$this->_dbw->rollBack();
			}
			error_log("[System::DB] Error on query $sql");
			throw new Exception($e->getMessage());
		}
	}

	/**
	* Retrieve list of possible values for a enum field in MySQL
	*
	* @param string $table Table to pull column from
	* @param string $col Column to pull values from
	* @return array
	*/
	public function enum($table,$col) {

        $this->_connect('read');

		try {
			$row = DBUtil::query('SHOW COLUMNS FROM '.$table.' LIKE :col',array('col'=>$col),TRUE);
		} catch (Exception $e) {
			error_log('[System] System::enum => '.$e->getMessage());
			throw new Exception($e->getMessage());
		}
		$items = $row ? explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2",$row['Type'])) : array(0 => 'None');
		return $items;
	}

	public function get_dbr(){
		$this->_connect('read');
		return $this->_dbr;
	}

}

?>

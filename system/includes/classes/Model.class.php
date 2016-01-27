<?php
/**
 * This is the Model class that allows Controllers to access the database
 * This class extends the DB class for easy access to DB functions
 *
 * @package		System
 * @author Edwin & Jason <uberlinuxguy@tulg.org>
 **/
// TODO: Model class will need some clean up and error checking, mostly in the constructor.
// TODO: Also need to double check the 'flow' of the Model class.
class Model extends DB {

	/** Name of the table that is being presented by the model */
	protected $_table = null;

	/** Primary Key for table model is for */
	protected $_key = null;

	/** Primary key id of the row that is currently being handled */
	protected $_id = null;

	/** Metadata for fields within the table we are dealing with */
	protected $_metadata = array();

	/** Class to fetch results as */
	protected $_fetchClass = null;

	/**
	* Constructor.  When a instance of the class is loaded it will
	* attempt to find the primary key of the table we are dealing with.
	* If a value is passed to the constructor it will be looked up in
	* the table against the primary key and if a row is found it will
	* be assigned into the object.
	*
	* @access	public
	* @param	mixed	$id					Id to lookup against primary key field
	* @return	void
	*/
	public function __construct($id = null) {
	    parent::__construct();

		if (!is_array($_ENV['describeTable'])) {
			$_ENV['describeTable'] = array();
		}

		# Make sure we have a table to look at
		if (is_null($this->_table)) {
			throw new Exception('No table defined for access in model');
		}

		# Verify Model is not being use directly, and setup _fetchClass
		$class = get_class($this);
		if ($class == 'Model') {
			throw new Exception('Model should not be used directly');
		}
		
		$this->_fetchClass = $class;

		# Check to see if we've already checked table layout this request
		if (array_key_exists($this->_table, $_ENV['describeTable'])) {
			$this->_key = $_ENV['describeTable'][$this->_table]['key'];
			$this->_metadata = $_ENV['describeTable'][$this->_table]['metadata'];
		} else {
			# Begin process of retrieving table layout
			$sql = "DESCRIBE " . $this->_table;
			$rows = $this->query($sql);

			# If we got back our rows then parse out the data
			if (is_array($rows)) {
				foreach ($rows as $row) {
					# Format of Type field is "datatype(length) attributes" ex. int(11) unsigned
					preg_match('/^([a-z]+)\(([0-9]+)\)(.*)/i', $row->Type, $matches);
					$this->_metadata[$row->Field] = new stdClass();
					if(count($matches) > 0) {
						
						$this->_metadata[$row->Field]->type = trim($matches[1]);
						$this->_metadata[$row->Field]->length = trim($matches[2]);
						$this->_metadata[$row->Field]->attr = trim($matches[3]);
						$this->_metadata[$row->Field]->extra = trim($row->Extra);
					} else { 
							$this->_metadata[$row->Field]->type = $row->Type;
					}
					# Check to see if the field we're dealing with is a primary key
					if (preg_match('/\bPRI\b/', $row->Key)) {
						$this->_key = $row->Field;
						$this->_metadata[$row->Field]->primary = true;
					}
				}
			}
			$_ENV['describeTable'][$this->_table] = array(
				'key' => $this->_key,
				'metadata' => $this->_metadata
			);
		}

		# If we know the primary key and have an id, fetch the row
		if (!empty($this->_key) and !is_null($id)) {
			$row = $this->get($id);

			

		}
	}

	/**
	* Magic method for retrieval of rows by fields existing in the table.
	*
	* Parameters for getBy<field> methods:
	*	string	criteria			What to match the field against
	*
	* @access	private
	* @param	string	$funcname			Name of function that was called
	* @param	array 	$params				Array of parameters that were passed
	* @return 	mixed
	*/
	public function __call($funcname, $params = array()) {
        parent::__call($funcname, $params);
		$fields = join('|', array_keys($this->_metadata));
		if (preg_match('/^getBy(' . $fields . ')$/i', $funcname, $match)) {
			$field = trim($match[1]);
			$value = trim($params[0]);
			$this->_where_clause = new WhereClause($field, $value);

//			$this->setFetchClass($this->_fetchClass);
			return $this->getWhere($this->_table);
		}
	}

	/**
	* Retrieve row from the table where the primary key matches the id given
	*
	* @access	public
	* @param	mixed	$id				Id to match against primary key
	* @return 	object
	*/
	public function get($id,$id_field=NULL) {
		// used in case the caller wants to specify an ID field to use.
		// for example if a table doesn't have a PRIMARY KEY.
		if($id_field != NULL ) {
			$this->_where_clause = new WhereClause($id_field, $id);
		} else {
			$this->_where_clause = new WhereClause($this->_key, $id);
		}

		$row = $this->getOneUsingWhere($this->_table);
		if(is_object($row)) {
			$this->_id = $id;
			foreach(array_keys($this->_metadata) as $field) {
				$this->{$field} = $row->{$field};
			}
		}
		return $row;
	}

	/**
	* Retrieve all rows from the table
	*
	* @access	public
	* @return 	array
	*/
	public function getAll() {
		$this->where_clause(NULL);
		$this->orderby(array($this->_key => "ASC"));
		return $this->getWhere($this->_table);
	}
	public function getOneUsingWhere($just_count=FALSE) {
		$result = $this->getWhere($this->_table,$just_count);
		if(is_array($result)){
			if(count($result)==0){
				//file_put_contents("/tmp/debug_backtrace.txt", print_r(debug_backtrace(), true));
				return NULL;
			}
			return $result[0];
		}
		return $result;
	}
	public function getUsingWhere() {
		return $this->getWhere($this->_table);
	}

	/**
	* Retrieve a set of rows based on the "page" they would be on.
	*
	* @access	public
	* @param	int		$page			Page number to retrieve items for
	* @param	int		$items			Number of items per page
	* @return 	array
	*/
	public function getPage($page = 1, $items = 25) {
		if(!is_numeric($page)) {
				$page=1;
		}
		$this->limit($items);
		API::DEBUG("[Model::getPage() page is $page, items is $items",7);
		$this->offset(($items * ($page - 1)));
		$tmp = $this->getWhere($this->_table);

		return $tmp;
	}

	public function getCount() {
		$this->limit(NULL);
		return $this->getOneUsingWhere(TRUE);
	}

	/**
	* Insert new row into the table
	*
	* @access	public
	* @param	array 	$data			Array containing data to be inserted into the table
	* @return 	bool
	*/
	public function insert($data) {
		foreach ($data as $key => $val) {
			if (!array_key_exists($key, $this->_metadata)) {
				unset($data[$key]);
			}
		}
		return $this->insertRow($this->_table, $data);
	}

	/**
	* Update row in the table
	*
	* @access	public
	* @param	array 	$data			Array containing data to be inserted into the table
	* @return 	bool
	*/
	public function update($data) {
		foreach ($data as $key => $val) {
			if (!array_key_exists($key, $this->_metadata)) {
				unset($data[$key]);
			}
		}

		return $this->updateRow($this->_table, $data);
	}

	/**
	* Delete rows from the table
	*
	* @access	public
	* @return 	bool
	*/
	public function delete() {

		$this->where_clause(new WhereClause($this->_key, $this->{$this->_key}));
		return $this->deleteRow($this->_table, false);
	}

	/**
	* Delete all rows from the table
	*
	* @access	public
	* @return	bool
	*/
	public function deleteAll() {
		return $this->deleteRow($this->_table, true);
	}

	/**
	* Save the current row being worked with into back to the table
	*
	* @access	public
	* @return 	bool
	*/
	public function save() {
		$data = array();
		foreach ($this->_metadata as $key => $val) {
			if (isset($this->$key) and $key != $this->_key) {
				$data[$key] = $this->$key;
			}
		}
		if (count($data) > 0) {
			$this->_where_clause = new WhereClause($this->_key, $this->{$this->_key});

			return $this->update($data);
		}
	}
}

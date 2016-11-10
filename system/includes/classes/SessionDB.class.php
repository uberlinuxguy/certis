<?php
class SessionDB extends Model {
	protected $_table = 'sessions';
	
	protected $sid;
	protected $mod_time;
	protected $data;
	
	public function __construct() {
		// call the Model class constructor.
		parent::__construct();
		
		// set the session save handler on instantiation.
        session_set_save_handler(array(&$this, 'open'),
                                array(&$this, 'close'),
                                array(&$this, 'read'),
                                array(&$this, 'write'),
                                array(&$this, 'destroy'),
                                array(&$this, 'gc'));
        // register the session_write_close function as a shutdown
        // function so that the instance of our session object
        // gets called before it's destroyed.
        register_shutdown_function('session_write_close');
	}
	
	function open($save_path, $session_name) {
		// Open just returns, nothing to see here... move along... move along...
	}	
	
	function close() {
		return true;
	}

	function read($id) {
		// Read in the session and check it quickly to make sure it's not expired.  
		// I Know gc does this, but this is a bit more reliable to enforce the lifetimes.
		API::DEBUG("[SessionDB::read()] In Function");
		$row = $this->get($id);
		if(is_object($row)) {
			$sess_time = strtotime($row->mod_time);
			$max_time = time() - ini_get('session.gc_maxlifetime');
			API::DEBUG("SessionDB::read()] sess_time = $sess_time, max_time = $max_time", 8);
			if(strtotime($row->mod_time) < time() - ini_get('session.gc_maxlifetime')) {
				API::DEBUG("[SessionDB::read()] Removing session " . $id . " due to expire.",8);
				$this->deleteRow($this->_table,false);
				return "";
			}
			
			
			return (string) $row->data;
		}
		return "";
	}
	
	function write($id, $data) {
		// Write the session out to the database
		$result = $this->get($id);
		
		$update['data'] = $data;
		$update['mod_time'] = date("c");
		if(!empty($result)) {
			$this->update($update);
		} else {
			$update['sid'] = $id;
			$this->insert($update);
		}
		return true;
	}
	
	function destroy($id) {
		$this->get($id);
		$this->delete();
		return true;
	}
	
	function gc($maxlifetime) {
		API::DEBUG("[SessionDB::gc()] In Function",8);
		$max_timestamp = date("c", time() - $maxlifetime);
		$tmp_where = new WhereClause('mod_time', $max_timestamp, "<");
		$this->where_clause($tmp_where);
		$this->deleteRow($this->_table,false);
		
		
	}
	
}
?>

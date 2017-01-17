<?php
class Certis extends StdClass{
	static $module; 
	static $action;
	static $req_id;
	static $params;
	static $config;
	static $authless;
	static $authed_user;
	static $exec_mode;
	static $CertisInst;
	static $activeSection;
	
	// protected array to hold over-loaded data
	private $ol_data = array();
	
	/**
	 * Function called from system_init.php to set up the whole request
	 * 
	 */
	public function initCertis() {
		// initialize the values to known quantities.
		self::$module = "";
		self::$action = "";
		self::$req_id = "";
		self::$params = array();
		self::$config = array();
		self::$authless = array();
		self::$authed_user = "";
		self::$exec_mode = "";
		self::$CertisInst =& $this;	// store a reference to ourself here 
		
		self::$activeSection = FALSE;

		// Set up the static variables from either the URL
		// or the CLI args/env.
		$this->setupStaticVars();

		// set up the session stuff
		$this->initSession();
		
		// initialize system Global objects
		$this->initGlobalObjects();
		
		// call up all the module initializers
		$this->callModInit();
		
		
	}
	
	public function initSession() {
		API::DEBUG("[Certis::initSession()] In function, exec mode is " . $this->exec_mode, 8);
		if($this->exec_mode == "web"){
			// set the cookie domain, if it's set in the conf
			if(isset($this->config->session_cookie)) {
		    	ini_set('session.cookie_domain', $this->config->session_cookie);
			}
			//set the lifetime of the GC if it's set.
			/*if(isset($this->config->session_lifetime)) {
				ini_set('session.gc_maxlifetime',$this->config->session_lifetime);
				// also set the cookie lifetime.  Cuz we can. :-)
				ini_set('session.cookie_lifetime', $this->config->session_lifetime);
			}*/
			
			// if the session_class is set, use that as the session manager.
			// NOTE: it is expected that the class calls 'session_set_save_handler' and
			// 		 any necessary functions for operation in it's contructor.
			if(isset($this->config->session_class)){
				$session_class_name = $this->config->session_class;
				$session_class=new $session_class_name;
			}
			
			// start the session.
			session_start();
			//API::DEBUG("[Certis::initSession()] authed user from session is ". $_SESSION['authed_user'],8);
			// create a blank entry in $_SESSION for authed user if it doesn't exist.
			if(!isset($_SESSION['authed_user'])) {
				$this->authed_user = '';
			} else {
				API::DEBUG("[Certis::initSession()] Setting Certis->authed_user", 8);
				$this->authed_user = $_SESSION['authed_user']; 
			}
			
		} 
		// TODO: Need to figure out a way to set this stuff up for CLI mode.
	}
	
	public function setupStaticVars() {
		
		// check the mode we should be in.
		// This is used by a few places to set up stuff differently
		// depending on what mode we are in.
		if(php_sapi_name() == 'cli') {
			$this->exec_mode = "cli";
		} else { 
			$this->exec_mode = "web";
		}
		global $conf;
		// bring in our config
		include_once(_SYSTEM_ . "/config/config.php");
		
		// Setup config static var
		$this->config = $conf;
		
		// now parse in the URL/ENV vars to our static vars.
		$this->parseMARP();		
		
		
	}
	
	public function parseMARP() {
		// Parses the Module, Action, RequestID and Params from the different possible
		// inputs methods ($_REQUEST, PrettyURLS, and CLI)
		$tmp_module = "";
		$tmp_action = "";
		$tmp_req_id = "";
		$tmp_params = array();
		// non-cli stuff is assumed to be web based.  
		if(php_sapi_name() != "cli") {
			if($this->config->pretty_urls === TRUE) {
				// filter out index.php if need be.
				$_SERVER['REQUEST_URI'] = preg_replace('/^\/index.php/', '', $_SERVER['REQUEST_URI']);
				$args = explode('/',$_SERVER['REQUEST_URI']);
	
				if (@count($args) > 0) {
					array_shift($args);
	
					// Setup name=value arguments from the URL
					if (@count($args) > 0) {
						foreach ($args as $key => $arg) {
							if (preg_match('/=/',$arg)) {
								$parts = explode('=',$arg);
								$tmp_params[$parts[0]] = trim($parts[1]);
								unset($args[$key]);
							}
						}
					}
	
					// now for the positional stuff
					// module is the first arg after index.php
					$tmp_module = array_shift($args);
	
					// if we have any more, let's continue
					if (@count($args) > 0) {
						// next positional arg is the action
						$tmp_action = array_shift($args);
						if (@count($args) > 0) {
							// and finally we have the id.
							$tmp_req_id = array_shift($args);
						}
	
						// everything else should be treated as boolean args.
						if (@count($args) > 0) {
							foreach ($args as $arg) {
								$tmp_params[$arg] = TRUE;
							}
						}
					}
				}
				// a few other checks
				if($tmp_req_id == "" ) {
					// see if it's in the request variables
					$tmp_req_id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : NULL;
				}
				// merge $_REQUEST into $tmp_params.
				$tmp_params = array_merge($tmp_params, $_REQUEST);
			} else { // end if for pretty URLS
				// non-pretty URL web mode.  Pull from $_REQUEST
				$tmp_module = (isset($_REQUEST['module'])) ? $_REQUEST['module'] : NULL;
				$tmp_action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : NULL;
				$tmp_req_id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : NULL;
				$tmp_params = (isset($_REQUEST['params'])) ? $_REQUEST['params'] : NULL;
			} 
			// This might be able to safely go outside the main if statement
			// in here if the templates are properly coded for commandline stuff,
			// which they more than likely aren't.
			$tmp_action = (empty($tmp_action)) ? 'index' : $tmp_action;
		} else {
			// TODO: pull in the args from the command line. 
			
		}
		
		// alright, we've gathered what we need, now set the vars.
		$this->module=$tmp_module;
		$this->action=$tmp_action;
		$this->req_id=$tmp_req_id;
		$this->params=$tmp_params;
	}
	
	public function initGlobalObjects(){
		global $global_objects;
		$global_objects = array();
		$obj_init = glob(_SYSTEM_.'/modules/*/objects.php');
		if (is_array($obj_init)) {
	    	foreach ($obj_init as $filename) {
	    	    include_once($filename);
	 	  }
		}
		foreach($global_objects as $class => $requisits) {
			if(!isset($this->{$class})) {
				$this->_initGlobalObject($class, $requisits, $global_objects);
			}
		}
		
	}
	
	private function _initGlobalObject($class, $requisits, $global_objects) {
		// process any prereqs this class has before
		// instantiating it.
		if(count($requisits > 0 )) {
			foreach ($requisits as $dep_class) {
				if(!isset($this->{$dep_class})) {
					initGlobalObject($dep_class, $global_objects[$dep_class], $global_objects);
				}
			}
		}
		// instantiate the class.
		API::DEBUG("Creating class $class", 7);
		$this->{$class} = new $class;
	}

	public function callModInit() {
		$modinit = glob(_SYSTEM_.'/modules/*/init.php');
		if (is_array($modinit)) {
		    foreach ($modinit as $filename) {
		        include_once($filename);
		   }
		}
		
	} 
	function __set($name, $value){
		// if the static version of this variable exists, 
		// then set the static version.
		if($name == "") {
			//error_log(print_r(debug_backtrace(), true));
		}
		if(isset(self::$$name)) {
			self::$$name = $value;
		} else {
			// otherwise set the non-static version.
			$this->ol_data[$name] = $value;
		}
		
	}
	
	function __get($name) {
		// if the static version exists, return that
		if(isset(self::$$name)) {

			return self::$$name; 
		} elseif (isset($this->ol_data[$name])) {
			// if the non-static version exists, return that.

			return $this->ol_data[$name];
		}
		// otherwise trigger a NOTICE error and return NULL
		trigger_error("Undefined Property via Certis->__get(): $name");
		API::DEBUG("ol_data dump: " . print_r($this->ol_data, true), 1);
		API::DEBUG("PHP Bactrace:". print_r(debug_backtrace(false), true),1 );
		return null;
	}
	
	function __isset($name) {
		// if the static version exists, return that
		if(isset(self::$$name)) {
			return TRUE; 
		} elseif (isset($this->ol_data[$name])) {
			// if the non-static version exists, return that.
			return TRUE;
		}
		return FALSE;
	}
	
	
}
?>

<?php
/**
* This is a utility class for the main system
*
* @package System
* @author Edwin & Jason <uberlinuxguy@tulg.org>
*/
class API extends Certis {

	/**
	* debug called if self::$config->debug == true. Logs via error_log
	* 
	* @static
	* @param string $message text to log
	* @access public
	*/	
	static public function DEBUG($message, $level=9) {
		if(self::$CertisInst->config->debug >= $level) { 
			error_log("[Certis::Debug Level $level]" . $message);
		}
	}

	/**
	* add an entry to the menu nav
	*
	* @static
	* @param int $priority a numberic priority to allow for ordering
	* @param string $link_txt text of the link that's going to be added
	* @param string $module name of the module we are going to add to the left nav
	* @param string $action (opitonal) the name of the action within the module to link to.
	* @access	public
	*/
	static public function addMenuItem($priority, $link_txt, $module, $action=NULL) {
		$linkClass = (self::$module == $module && $module != strtolower(self::$config->auth_class)) ? "leftNavLinkActive" : "leftNavLink";
		self::$activeSection = (((self::$module == $module) || self::$activeSection)&& ($module != strtolower(self::$config->auth_class)))  ? TRUE : FALSE; 
		// if the auth class exists, instantiate it.
		if(class_exists(self::$CertisInst->config->auth_class)) {
			if(!in_array($module,self::$CertisInst->authless)) {
				$classname = self::$CertisInst->config->auth_class;
				$auth_mod = new $classname;
				if(isset(self::$CertisInst->authed_user)) {
					if($auth_mod->validateUID(self::$CertisInst->authed_user)) {
						$new_priority = $priority;
						$direction=-1;
						while(array_key_exists($new_priority, $GLOBALS['leftNav'])){
							if($new_priority<=1){ 
								$direction=1;
								$new_priority=$priority;
							}
							$new_priority+=$direction;
						}
						$GLOBALS['leftNav'][$new_priority] = '<div class="' . $linkClass . '">' . '<a class="' . $linkClass . '" priority="' . $new_priority . '" href="' .
							API::printUrl($module, $action) .  	'">' . $link_txt . '</a></div>';
					} else {
						API::DEBUG("[API::addMenuItem()] Unable to validate uid '" . self::$CertisInst->authed_user . "'");
					}
				} else {
					API::DEBUG("[API::addMenuItem()] Non-Authless module $module and no authed_user set.");
				}
				
				//API::DEBUG("[API::addMenuItem()] leftNav now equals: " . print_r($GLOBALS['leftNav'][$priority] , true));
				unset($auth_mod);
				return;
			}
		}
		$GLOBALS['leftNav'][$priority] = '<a class="' . $linkClass . '" href="' .
			API::printUrl($module, $action) .  	'">' . $link_txt . '</a>';
		API::DEBUG("[API::addMenuItem()] leftNav now equals: " . print_r($GLOBALS['leftNav'][$priority] , true));


	}

	/**
	* add a module that won't require athentication
	*
	* @static
	* @param string $module module to add to the authless array.
	* @access	public
	*/
	static public function addAuthlessModule($module) {
		if($module != "") {
			self::$CertisInst->authless[] = $module;
		}
	}


	/**
	* Add an error on to the session error stack
	*
	* @static
	* @access	public
	* @param	string	$error				Error Message to be added to the stack
	* @return	bool
	*/
	static public function Error($error) {
		if (!is_array($_SESSION['error'])) {
			$_SESSION['error'] = array();
		}
		if(is_array($error)) {
				$_SESSION['error'] = array_merge($_SESSION['error'], $error);
		} else {
			array_push($_SESSION['error'],$error);
		}
	}

	/**
	* Determine if there are any errors in the stack
	*
	* @static
	* @access	public
	* @return	bool
	*/
	static public function hasErrors() {
		if(isset($_SESSION['error'])){
			if (is_array($_SESSION['error'])) {
				if (count($_SESSION['error']) > 0) {
					return TRUE;
				}
			}
			
		}

		return FALSE;
	}
	/**
	* Add a message on to the session message stack
	*
	* @static
	* @access	public
	* @param	string	$u_msg				Message to be added to the stack
	* @return	bool
	*/
	static public function Message($u_msg) {
		if (!is_array($_SESSION['msgs'])) {
			$_SESSION['msgs'] = array();
		}
		array_push($_SESSION['msgs'],$u_msg);
	}

	/**
	* Determine if there are any messages in the stack
	*
	* @static
	* @access	public
	* @return	bool
	*/
	static public function hasMsgs() {
		if(isset($_SESSION['msgs'])){
			if (is_array($_SESSION['msgs'])) {
				if (count($_SESSION['msgs']) > 0) {
					return TRUE;
				}
			}
			
		}

		return FALSE;
	}
	
	/**
	* Perform a redirect and make sure to exit afterwards
	* @access	public
	* @param	string	$url				URL the user will be redirected to
	*/
	static public function Redirect($url) {

		header('Location: '.$url);
		exit;
	}

	/**
	* Calls the hooks for this module/action/subaction
	*
	* @access	public
	* @param	string		$module		the module that is calling it's hooks.
	* @param 	string		$action		the hook action that is being called
	* @param	string		$subaction	the subaction to call.
	* @param 	string 		$data		data to pass into the hooks being called.
	* @return 	bool
	*/
	static public function callHooks($module, $action, $subaction, &$data=NULL) {
		$hook_files = glob(_SYSTEM_ . '/modules/*/hooks/' . $module . '/' . $action . '/'. $subaction . ".php");
		if(is_array($hook_files)) {
			foreach($hook_files as $hook_file) {
				include $hook_file;
				error_log("Calling hook: " . $hook_file);
			}
		}
	}

	/**
	 * Gernerate URLs Based off module, action, id, and extra info.  This function will
	 * allow the user to switch between pretty URLs and non-pretty URLs on the fly by
	 * changing the config option.
	 *
	 * @access 	public
	 * @param	string	$module		the module to used to generate the URL
	 * @param	string	$action		the action to used to generate the URL
	 * @param	string	$id			the id used to generate the URL
	 * @param 	array 	$extra		any extra information to the URL, in key => value array form
	 * @return 	string 	the url
	 */

	static public function printUrl($module=NULL, $action=NULL, $id=NULL, $extra=NULL) {


		$url = "/";
		if(self::$config->pretty_urls === TRUE) {

			// process our positionals
			$url .= "index.php/";

			// if there is no $module set, then just return /
			if(!strlen($module)) {
				return $url;
			}
			$module = preg_replace("/^\//","", $module);
			$url .= $module . "/";
			if(!strlen($action)) {
				return $url;
			}
			$url .= $action . "/";
			if($id != NULL) {
				$url .=  $id . "/";
			}


			// now the extras arg which could be an array or a string.
			if(!empty($extra)) {
				if(is_array($extra)) {
					foreach($extra as $key => $value) {
						$url .= "$key=$value/";
					}
				} else {
					$url .= $extra . "/";
				}
			}
		} else {
			$url .= "?";
			if(!strlen($module)) {
				return "/";
			}
			$url .=  "module=" . urlencode($module) . "&";
			if(!strlen($action)) {
				return $url;
			}

			$url .=  "action=" . urlencode($action) . "&";
			if(!strlen($id)) {
				return $url;
			}
			$url .= "id=" . urlencode($id) . "&";

			if(is_array($extra)) {
				foreach($extra as $key => $value) {
					$url .= urlencode($key) . "=" . urlencode($value) . "&";
				}
			} else {
				if($extra != NULL) {
					$url .= urlencode($extra) . "&";
				}
			}
		}

		return $url;
	}

}

?>

<?
/*
 * Auth_LDAP - Class to instantiate an Auth_LDAP object.  Does 
 *            not extend Model because we don't need it to.
 */
global $conf;
class Auth_LDAP extends Certis {
	protected $ldap=NULL;

	private function _searchUser($login, $filter=""){
		global $conf;
		# Search for user
		if($filter == ""){
			$filter = "(&(objectClass=person)(" . $conf->auth_ldap->username_attr . "={search}))";
		}
		$filter = str_replace("{search}", $login, $filter);
		error_log($filter);
		$search = ldap_search($this->ldap, $conf->auth_ldap->base, $filter);
		error_log(print_r($search, true));
		$errno = ldap_errno($this->ldap);
		if ( $errno ) {
			error_log("LDAP - Search error $errno  (".ldap_error($this->ldap).")");
			return NULL;
		} else 	{

			# Get user DN
			$entry = ldap_first_entry($this->ldap, $search);
			error_log("Entry");	
			error_log(print_r($entry, true));
			$userdn = ldap_get_dn($this->ldap, $entry);
			if( !$userdn ) {
				error_log("LDAP - User " . $login . " not found");
				return false;
			} else {
				$user_attrs = ldap_get_attributes($this->ldap, $entry);
				$user_attrs_cpy = array();
				// XXX: Probably fine for most attributes we will use.
				foreach($user_attrs as $key => $value){
					$user_attrs_cpy[$key] = $value[0];
				}
				$retval = array();
				array_push($retval, $userdn);
				array_push($retval, $user_attrs_cpy);
				return $retval;
			}
		}
		return NULL;

	}
	
	private function _connectLDAP(){
		global $conf;
		# Connect to LDAP
		$this->ldap = ldap_connect($conf->auth_ldap->url);
		ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0);
		if ( $conf->auth_ldap->starttls && !ldap_start_tls($this->ldap) ) {
			error_log("LDAP - Unable to use StartTLS");
			return false;
		} else {

			# Bind
			if ( isset($conf->auth_ldap->binddn) && isset($conf->auth_ldap->bindpw) ) {
				$bind = ldap_bind($this->ldap, $conf->auth_ldap->binddn, $conf->auth_ldap->bindpw);
			} else {
				$bind = ldap_bind($this->ldap);
			}

			$errno = ldap_errno($this->ldap);
			if ( $errno ) {
				error_log("LDAP - Bind error $errno  (".ldap_error($this->ldap).")");
				return false;
			}
		}
		return true;


	}

	/**
	 * Used to vaidate a user's credentials. (uname, password)
	 * @param	array	$creds	the uname and password passed in as an array.
	 * @return 	bool
	 */
	function validateCredentials($creds) {
		global $conf;

		if(!$this->_connectLDAP()) {	
			return false;
		} else {
			# see if you can find the user
			$search_res = $this->_searchUser($creds['uname']);
			if($search_res != NULL) {
				if(!is_array($search_res)) {
					error_log("LDAP - Something went wrong with the LDAP search.");
					return false;
				}
				# get the user attributs
				$userdn = $search_res[0];
				$user_attrs = $search_res[1];

				# Bind with old password
				error_log("UserDN: " . $userdn);
				$bind = ldap_bind($this->ldap, $userdn, $creds['password']);
				$errno = ldap_errno($this->ldap);
				if ( ($errno == 49) && $ad_mode ) {
					if ( ldap_get_option($this->ldap, 0x0032, $extended_error) ) {
						error_log("LDAP - Bind user extended_error $extended_error  (".ldap_error($this->ldap).")");
						$extended_error = explode(', ', $extended_error);
						if( strpos($extended_error[2], '773') ) {
							error_log("LDAP - Bind user password needs to be changed");
							$errno = 0;
							return false;
						}
						if( strpos($extended_error[2], '532') and $ad_options['change_expired_password'] ) {
							error_log("LDAP - Bind user password is expired");
							$errno = 0;
							return false;
						}
						unset($extended_error);
					}
				}
				if ( $errno ) {
					error_log("LDAP - Bind user error $errno  (".ldap_error($this->ldap).")");
					return false;
				} else {
					// got a good bind, user is valid.  Let's populate some stuff
					$this->authed_user = $user_attrs[$conf->auth_ldap->uid_attr];
					$names = array ();
					$names['fname'] = $user_attrs[$conf->auth_ldap->fname_attr];
					$names['lname'] = $user_attrs[$conf->auth_ldap->lname_attr];
					$prefs = new Prefs();
					if($prefs->checkUID($this->authed_user, $conf->prefs_auto, NULL, $names)){
						$_SESSION['authed_user'] = $this->authed_user;
						API::Debug("auth_ldap: checkUID passed");
						return true;
					} else {
						API::Error("Username Not Valid in system. Error: 3304");
					}
				
				}
			}
				
		}
		return FALSE;
	}

	/**
	 * Validate a given UID as existing in the DB
	 * @param 	integer		$uid	the uid to validate
	 * @return 	bool
	 */
	function validateUID($uid) {
		global $conf;
		if($uid == 0 ) { return FALSE; }
		API::DEBUG("[Auth_LDAP::validateUID] \$uid = $uid");
		if(!$this->_connectLDAP()) {	
			return false;
		} else {
			# see if you can find the user
			$search_res = $this->_searchUser($uid, "(&(objectClass=person)(" . $conf->auth_ldap->uid_attr . "={search}))");
			if($search_res != NULL) {
				if(!is_array($search_res)) {
					error_log("LDAP - Something went wrong with the LDAP search.");
					return false;
				}
				# get the user attributs
				$userdn = $search_res[0];
				$user_attrs = $search_res[1];
				if($user_attrs[$conf->auth_ldap->uid_attr] == $uid ){
					return true;
				}
			}
		}

		return false;

	}

	/**
	 * List known users.
	 * @return array 
	 */
	function listUsers() {
		global $conf;
		$user_array=array();
		if(!$this->_connectLDAP()) {	
			return false;
		} else {
			
			# limit the attributes we are going to look for.
			$filter="(objectClass=person)";
			$attrs_filter = array($conf->auth_ldap->username_attr);
			$search = ldap_search($this->ldap, $conf->auth_ldap->base, $filter, $attrs_filter);
			$errno = ldap_errno($this->ldap);
			if ( $errno ) {
				error_log("LDAP - Search error $errno  (".ldap_error($this->ldap).")");
				return NULL;
			} else 	{
				$users = ldap_get_entries($this->ldap, $search);
				if( !is_array($users) ) {
					error_log("LDAP - Search for users failed.");
					return false;
				} else {
					for($i=0; $i<$users['count']; $i++){
						array_push($user_array, $users[$i][$conf->auth_ldap->username_attr][0]);
					}
					sort($user_array);
					return $user_array;
				}
			}
		}

		return false;

		/*
		// this function has a shell call to ypcat cuz I 
		//      don't see a PHP function for this.
		$output = array();
		exec('ypcat passwd | awk -F\: \'{print $1}\'', $output);
		sort($output);
		return($output);
		*/
		return NULL;
		

	}
	
	/**
	 * Get the UID of a username.
	 * @param 	string	$uname	the uname to get the id for
	 * @return 	int
	 */

	function getUID($uname) {
		global $conf;
		API::DEBUG("[Auth_LDAP::getUID] \$uname = $uname");
		$retval = $this->getUserByName($uname);
		if(is_array($retval)) {
			return $retval['uid'];
		}
		return -1;
	}
	
	/**
	 * Get user info from ID
	 * @param 	int 	$uid	the uid to get info for.
	 * @return 	array|bool	false on error, array of info
	 */
	function getUserByUID($uid){ 
		global $conf;
		if($uid == 0 ) { return FALSE; }
		API::DEBUG("[Auth_LDAP::validateUID] \$uid = $uid");
		if(!$this->_connectLDAP()) {	
			return false;
		} else {
			# see if you can find the user
			$search_res = $this->_searchUser($uid, "(&(objectClass=person)(" . $conf->auth_ldap->uid_attr . "={search}))");
			if($search_res != NULL) {
				if(!is_array($search_res)) {
					error_log("LDAP - Something went wrong with the LDAP search.");
					return false;
				}
				# get the user attributs
				$userdn = $search_res[0];
				$user_attrs = $search_res[1];
				$userPosixAttrs = array();
				$userPosixAttrs['name'] = $user_attrs[$conf->auth_ldap->username_attr];
				$userPosixAttrs['passwd'] = ""; // always empty
				$userPosixAttrs['uid'] = $user_attrs[$conf->auth_ldap->uid_attr];
				$userPosixAttrs['gid'] = $user_attrs[$conf->auth_ldap->gid_attr];
				$userPosixAttrs['gecos'] = $user_attrs[$conf->auth_ldap->fname_attr] . " " .  $user_attrs[$conf->auth_ldap->lname_attr];
				$userPosixAttrs['dir'] = $user_attrs[$conf->auth_ldap->hdir_attr];
				$userPosixAttrs['shell'] = $user_attrs[$conf->auth_ldap->shell_attr];
				return $userPosixAttrs;

			}
		}
		return false;
	}
	 
	
	/**
	 * Get user info from Uname
	 * @param	int		$uname	the username to get info for.
	 * @return array|bool	false on error, array of info
	 */
	function getUserByName($uname){
		global $conf;
		API::DEBUG("[Auth_LDAP::getUserByName] \$uname = $uname");
		if(!$this->_connectLDAP()) {	
			return false;
		} else {
			# see if you can find the user
			$search_res = $this->_searchUser($uname);
			if($search_res != NULL) {
				if(!is_array($search_res)) {
					error_log("LDAP - Something went wrong with the LDAP search.");
					return false;
				}
				# get the user attributs
				$userdn = $search_res[0];
				$user_attrs = $search_res[1];
				$userPosixAttrs = array();
				$userPosixAttrs['name'] = $user_attrs[$conf->auth_ldap->username_attr];
				$userPosixAttrs['passwd'] = ""; // always empty
				$userPosixAttrs['uid'] = $user_attrs[$conf->auth_ldap->uid_attr];
				$userPosixAttrs['gid'] = $user_attrs[$conf->auth_ldap->gid_attr];
				$userPosixAttrs['gecos'] = $user_attrs[$conf->auth_ldap->fname_attr] . " " .  $user_attrs[$conf->auth_ldap->lname_attr];
				$userPosixAttrs['dir'] = $user_attrs[$conf->auth_ldap->hdir_attr];
				$userPosixAttrs['shell'] = $user_attrs[$conf->auth_ldap->shell_attr];
				return $userPosixAttrs;

			}
		}
		return false;
	}

}

?>

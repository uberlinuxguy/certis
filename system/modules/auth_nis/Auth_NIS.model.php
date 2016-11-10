<?php
/*
 * Auth_NIS - Class to instantiate an Auth_NIS object.  Does 
 *            not extend Model because we don't need it to.
 */

class Auth_NIS extends Certis{
	/**
	 * Used to vaidate a user's credentials. (uname, pass)
	 * @param	array	$creds	the uname and password passed in as an array.
	 * @return 	bool
	 */
	function validateCredentials($creds) {
		$pwent = posix_getpwnam(strtolower($creds['uname']));
		if($pwent == false ) {
			API::Error("Invalid Username/Password");
		}
		$cryptpw = crypt($creds['password'], $pwent['passwd']);
		if($cryptpw == $pwent['passwd']){ 
			API::DEBUG("[Auth_NIS::validateCredentials] returning TRUE",8);
			$_SESSION['authed_user'] = $pwent['uid'];
			$this->authed_user = $pwent['uid'];
			$names = explode(" ", $pwent['gecos'], 2);
			$names['fname'] = $names[0];
			$names['lname'] = $names[1];
			unset($names[1]);
			unset($names[0]);
			$prefs = new Prefs();
			if($prefs->checkUID($this->authed_user, $this->config->prefs_auto, NULL, $names)){
				return TRUE;
			}else {
				API::Error("Username Not Valid in system. Error: 3304");
				
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
		if($uid == 0 ) { return FALSE; }
		API::DEBUG("[Auth_NIS::validateUID] \$uid = $uid");
		$pwent = posix_getpwuid($uid);
		if ( $pwent != false ) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * List known users.
	 * @return array 
	 */
	function listUsers() {
		// this function has a shell call to ypcat cuz I 
		//      don't see a PHP function for this.
		$output = array();
		exec('ypcat passwd | awk -F\: \'{print $1}\'', $output);
		sort($output);
		return($output);
		
		

	}
	
	/**
	 * Get the UID of a username.
	 * @param 	string	$uname	the uname to get the id for
	 * @return 	int
	 */

	public function getUID($uname) {
		$tmp_info = posix_getpwnam($uname);
		return $tmp_info['uid'];
	}
	
	/**
	 * Get user info from ID
	 * @param 	int 	$uid	the uid to get info for.
	 * @return 	array|bool	false on error, array of info
	 */
	public function getUserByUID($uid){ 
		return posix_getpwuid($uid);
	}
	 
	
	/**
	 * Get user info from Uname
	 * @param	int		$uname	the username to get info for.
	 * @return array|bool	false on error, array of info
	 */
	public function	getUserByName($uname){
		return posix_getpwnam($uname);
	}
}

?>

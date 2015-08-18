<?

/**
* This is the class to instantiate a Perms model object
* for the Auth module.
*
* @package Modules
* @subpackage Auth
* @author Jason <uberlinuxguy@tulg.org>
*/
class Perms extends Model {
	protected $_table = 'perms';

	/**
	 * registers a permission into the authentication system.  Usually usede in
	 * module init.php files
	 * @param 	string 	$name 	internal name of the permission to add
	 * @param 	string 	$descr 	a human-readable name for the permission, used for display
	 *
	 */

	function registerPerm($name, $descr) {
		// first check to see if the perm exists.
		$perm_info = $this->getPerm($name);

		if(is_object($perm_info)) {
			if(preg_match('/![a-zA-Z0-9_]/', $name) ) {
				error_log("[System::Perms::registerPerm()]: Unable to add permission $name.  Name is illeagal.");
			}
		} else {
				$insert = array();
				$insert['name'] = $name;
				$insert['descr'] = $descr;
				$this->insert($insert);
		}
	}

	/**
	 * Get's info for a perm from the database
	 * @param 	string	$name 	internal name of the perm to grab
	 * @return 	object 	an object representing the permission.
	 */
	function getPerm($name) {
		$this->where_clause(new WhereClause('name', $name));
		return $this->getOneUsingWhere();
	}
	
	function getAllPerms() {
		$tmp_perms = $this->getAll();
		sort($tmp_perms);
		// This little block will unset perms that this user 
		// doesn't have permission to.
		foreach($tmp_perms as $key => $perm) {
			if(!$this->checkPerm($this->authed_user, $perm->name)) {
				unset($tmp_perms[$key]);
			}
			
		}
		
		// check to see if they are a sys admin, and if they are, give them
		// the ability to set this on other users.
		if($this->checkPerm($this->authed_user, 'sys_admin')) {
			$tmp_perm = new stdClass();
			$tmp_perm->id=-1;
			$tmp_perm->name='sys_admin';
			$tmp_perm->descr='System Administrator';
			$tmp_perms[] = $tmp_perm;
		}
		return $tmp_perms;
	}

	/**
	 * get a permission's ID value
	 * @param 	string 	$name 	the internal name of the perm you want the id for
	 * @return 	integer 		the id of the perm requested
	 */
	function getPermValue($name) {

		$perm = $this->getPerm($name);
		return $perm->id;

	}

	/**
	 * Check to see if a user has a permission set
	 * @param 	integer 	$uid	the user id to check
	 * @param	string		$perm	the perm to check for
	 */
	function checkPerm($uid, $perm) {
		if ($uid <= 0) {
			return FALSE;
		}

		// get the user's prefs info
		$prefs = new Prefs();
		
		// check to make sure the user has some prefs, if not
		// this will fill in defaults.
		$prefs->checkUID($uid,true);
		
		$prefs->where_clause(new WhereClause('uid', $uid));
		$user_info = $prefs->getUsingWhere();
		$user_info = $user_info[0];
		
		

		// first, check to see if the user is a System Admin
		// System Admin is a special perm with a value of -1
		if($user_info->perms == -1) {
			// *ding* *ding* *ding* WE HAVE A SYS ADMIN!
			// nothing further here, just return.
			return TRUE;
		}

		// if we are requesting sys_admin perms, but are not
		// a sys admin, then return false.
		if($perm == 'sys_admin' && $user_info->perms != -1) {
			return FALSE;
		}

		// anything else is a db based perm, let's pull it.
		// get this perm's info

		$this->where_clause(new WhereClause('name', $perm));
		$perm_info = $this->getOneUsingWhere();


		// now let's check this user's perms for the
		// perm requested
		if(is_object($perm_info)) {
			if($user_info->perms & (1 << $perm_info->id) ) {
				return TRUE;
			}
		}
		// anything else, return false.
		return FALSE;



	}

	/**
	 * Set a user's perms value.
	 * @param 	integer		$perm_value		an already assembled bit-wise permissions value
	 * @param	integer		$uid			the UID to set the permissions for.
	 */
	function setUserPerms($perm_value, $uid) {
		$user_info = new Prefs($uid);

		$user_info->perms = $perm_value;

		$user_info->save();


	}
	function getUserPerms($uid, $auth_mod=NULL) {
		
		if($auth_mod == NULL) {
			$auth_mod = self::$config->auth_class;
		}
		$prefs = new Prefs($uid);
		API::DEBUG("[Perms::getUserPerms()] uid = $uid, auth_mod = $auth_mod");
		
		$tmp_where = new WhereClause('uid', $uid);
		$tmp_where->w_and('auth_mod', $auth_mod);
		$prefs->where_clause($tmp_where);
		$perms = $prefs->getUsingWhere();
		
		return $perms;
	}
}

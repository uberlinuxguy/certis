<?
/**
* This is the class to instantiate a Auth model object
* for the Auth module.
*
* @package Modules
* @subpackage Auth
* @author Jason <uberlinuxguy@tulg.org>
*/
class Auth extends Model {
	protected $_table = 'auth';


	/**
        * checks the input for validity according to what should be in the table
        * returning an array of errors or NULL if no errors occur.
        * NOTE: This function may modify it's argument.
        *
        *@access    public
        *@param     array   $data   the array of data to check
        *@return    array
        */
        public function check_input(&$data) {
		$errors=array();


		if($this->action == 'edit') {
			// edit function.

		} else{
			// in a create action
			$data['name'] = $data['uname'];

		}

		// password is saved no matter what,so let's check it.
		if($data['password'] != $data['password2'] ) {
			$errors[] = "Passwords do not match"; 
		}

		$data['password'] = md5($data['password']);
		
		//TODO: put in password security restrictions.


		if(count($errors)){
			return $errors;
		} 
		return NULL;

	}


	/**
        * sets the proper elements from $data into the fields on this instance of the model
        *
        *@access    public
        *@param     array   $data   the array of data to set
        *@param     bool    $insert Is this an insert or an update?
        *@param         string  $auth_mod       The authmod this person should be updated for.
        */
        public function set_data($data, $insert=0, $auth_mod=NULL){


                if($auth_mod==NULL){
                        $auth_mod = self::$config->auth_class;
                }
		if($insert === TRUE) {
			return $this->insert($data);

            	} else {
                	$where_tmp = new WhereClause('uid', $data['id']);
        	        $this->where_clause($where_tmp);
                	API::DEBUG("[Prefs::set_data()] data is " . print_r($data, true),8);
	                $this->update($data);
        	        API::Message("User Authentication Information Saved!");
                	if(isset($do_redirect)){
                        	API::Redirect($do_redirect);
	                }
        	        return NULL;
            	}

	}


	/**
	 * Used to vaidate a user's credentials. (uname, pass)
	 * @param	array	$creds	the uname and password passed in as an array.
	 * @return 	bool
	 */
	function validateCredentials($creds) {
		$where_clause = new WhereClause('name', $creds['uname']);
		$where_clause->w_and('password', $creds['password']);
		$this->where_clause($where_clause);
		$user_info = $this->getOneUsingWhere();

		if($user_info != NULL) {
			if(is_object($user_info)) {
				if($user_info->uid > 0) {
					$_SESSION['authed_user'] = $user_info->uid;
					$this->authed_user = $user_info->uid;
					return TRUE;
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

		$this->where_clause(new WhereClause('uid', $uid));
		$this->fields('uid');
		$user_id = $this->getOneUsingWhere();
		if($user_id != NULL) {
			if(is_numeric($user_id)) {
				return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	 * List known users.
	 * @return array 
	 */
	function listUsers() {
		
		$output = array();
		// reset the where clause to nothing.
		$this->where_clause(NULL);
		$this->fields('name');
		$output = $this->getWhere();
		sort($output);
		return($output);
	}
	
	/**
	 * Get the UID of a username.
	 * @param 	string	$uname	the uname to get the id for
	 * @return 	int
	 */

	public function getUID($uname) {
		$this->where_clause(new WhereClause('name', $uname));
		$this->fields('uid');
		return $this->getOneUsingWhere();
		
	}

	/**
	 * Get user info from ID
	 * @param 	int 	$uid	the uid to get info for.
	 * @return 	array|bool	false on error, array of info
	 */
	public function getUserByUID($uid){
		$this->where_clause(new WhereClause('uid', $uid));
		return $this->getOneUsingWhere();
	}
	 
	
	/**
	 * Get user info from Uname
	 * @param	int		$uname	the username to get info for.
	 * @return array|bool	false on error, array of info
	 */
	public function	getUserByName($uname){
		$this->where_clause(new WhereClause('name', $uname));
		return $this->getOneUsingWhere();
	}
}



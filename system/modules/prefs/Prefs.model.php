<?
    class Prefs extends Model {
        protected $_table = 'prefs';
        
        
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
			if($this->action != 'edit') {
/*
            	if(preg_match('/![a-zA-Z0-9.-_]/', $data['uname'])) {
                	$errors[] = "Invalid User Name!";
            	}

	            $this->where_clause(new WhereClause('uname', $data['uname']));
    	        if($this->getAll() != NULL) {
        	    	$errors[] = "Username already exists!.";
            	}

				if(strlen($data['uname']) > 32) {
					$errors[] = "Username too long!";
				}*/
			}
			$security_violation=false;
			if($this->action == 'edit') {
				if(isset($data['set_perms'])) {
					if(!self::$CertisInst->Perms->checkPerm($this->authed_user, 'perms_admin')) {
						$errors[] = "You do not have permissions to change permissions.";
						$security_violation=true;
						error_log("[Prefs::check_input()] Security Violation (perms_admin) ERR_SEC.  ");
					}
				} elseif($data['uid'] != $this->authed_user) {
					if(!self::$CertisInst->Perms->checkPerm($this->authed_user, 'user_admin')) {
						$errors[] = "You do not have permissions to change other user's info.";
						$security_violation=true;
						error_log("[Prefs::check_input()] Security Violation (user_admin) ERR_SEC.  ");
						
					}
				}
				

			}
            if(count($errors) > 0 ) {
            	// slightly different redirect here, because security issues redirect 
            	// us to index.
            	if($security_violation === TRUE) { 
            		// security violation, log some info.
					error_log("                         - IP " . $_SERVER['REMOTE_ADDR']);
					error_log("                         - user " . $this->authed_user);
					error_log("                         - session destroyed.(ERR_SEC_DESTROY)");
					// effectively log off the user
					$_SESSION['authed_user']=NULL;
					$this->authed_user = NULL;
					// set display messages to the user.
					API::Error($errors);
					// redirect them to the home page.
					API::Redirect("/");
						
            	}
            	
                return $errors;


            }
            else {
                return NULL;
            }
        }

        /**
        * sets the proper elements from $data into the fields on this instance of the model
        *
        *@access    public
        *@param     array   $data   the array of data to set
        *@param     bool    $insert Is this an insert or an update?
        *@param		string	$auth_mod	The authmod this person should be updated for.
        */
        public function set_data($data, $insert=0, $auth_mod=NULL){
        	
        	
        	if($auth_mod==NULL){ 
        		$auth_mod = self::$config->auth_class;
        	}
        	

        	if(isset($data['set_perms'])) {
				if(!self::$CertisInst->Perms->checkPerm($this->authed_user, 'perms_admin')) {
					error_log("[Prefs::check_input()] Security Violation (perms_admin) ERR_SEC.  ");
					// effectively log off the user
					$_SESSION['authed_user']=NULL;
					$this->authed_user = NULL;
					// set display messages to the user.
					$_SESSION['errors'] = $errors;
					// redirect them to the home page.
					API::Redirect("/");
				}
				$perms=0;
				if(isset($data['perms'])){
					foreach($data['perms'] as $perm) {
						if($perm == -1 ) {
							$perms = -1;
							continue;
						}
						$perms = $perms | (1 << $perm);
					}
				}
				$data['perms'] = $perms;
				unset($data['set_perms']);
				$do_redirect =API::printUrl("perms", "display", NULL, "uid=" . $data['uid']);
				
			}
            if($insert === TRUE) {
				return $this->insert($data);

            } else {
            	$where_tmp = new WhereClause('uid', $data['uid']);
            	$where_tmp->w_and('auth_mod', $auth_mod);
            	$this->where_clause($where_tmp);
            	API::DEBUG("[Prefs::set_data()] data is " . print_r($data, true),8);
                $this->update($data);
                API::Message("User Information Saved!");
                if(isset($do_redirect)){
                	API::Redirect($do_redirect);
                }
                return NULL;
            }
        }
        
                
        /**
        * check to see if this user has a prefs entry, and optionally create 
        * one if they don't
        *
        *@access    public
        *@param     int		$uid   		the uid to look for
        *@param     bool    $create		automagically create prefs entry? (default:false)
        *@param		string	$auth_mod	the auth mod they should be found under
        *@param		array	initial_data the initial stuff to populate prefs with.
        *@return	bool
        */
        public function checkUID($uid, $create=false, $auth_mod=NULL, $initial_data=NULL){
   	
		if($auth_mod==NULL){
        		$auth_mod = self::$config->auth_class;
        	}

        	$data=array('fname' => '', 'lname' => '', 'perms' => 0, 'auth_mod' => $auth_mod, 'uid' => $uid);
        	
        	// for now, only set fname and lname, perms should remain 0 until set by an admin.
        	if(is_array($initial_data)) {
        		$data['fname'] = $initial_data['fname'];
        		$data['lname'] = $initial_data['lname'];
        	}
            	API::DEBUG("[Prefs::checkUID()] " . print_r($data, true),1);

        	
        	$where_tmp = new WhereClause('uid', $uid);
            	$where_tmp->w_and('auth_mod', $auth_mod);
           
            	$this->where_clause($where_tmp);
        	$results = $this->getUsingWhere();
            	if(count($results) > 1) {
            		API::DEBUG("[Prefs::checkUID()] Multiple results returned for '$uid' and '$auth_mod'.");
            		API::DEBUG("[Prefs::checkUID()] This is bad because I am using the first one.");
            
            }
        	if(count($results) < 1) {
        			if($create === TRUE) {
	        			// create the entry.
    	    			
        				$this->set_data($data,true);
        				return TRUE;
        			}
        			return FALSE;
        			
        	}
        	return TRUE;
        }


    }

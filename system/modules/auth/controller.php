<?

/**
* This is the class to instantiate a AuthController object
* for the Auth module.  Set the 'auth_class' variable in the
* config file to use the 'Auth' module to use this code. This
* does not extend DefaultController because it doesn't need any of the
* default behavior.
*
* @package Modules
* @subpackage Auth
* @author Jason <uberlinuxguy@tulg.org>
*/
class AuthController extends DefaultController {

    public function __construct() {
        parent::__construct();
        # initialize the auth controller
	$this->req_id = (isset($this->req_id)) ? $this->req_id : '';

	
        $this->_model = new Auth((is_numeric($this->req_id) ? $this->req_id : NULL));
	$this->activeSection='auth';
    }

	public function authCheckAction() {
		if(!isset($this->authed_user)) {
			API::Redirect(API::printUrl('auth', 'login'));
		} else {
			if(!$this->_model->validateUID($this->authed_user)) {
				API::Redirect(API::printUrl('auth', 'login'));
			}
		}
	}

	public function loginAction() {
		if(isset($_POST['login'])) {					
			$creds = array();
			$creds['uname'] = $_POST['uname'];
			$creds['password'] = md5($_POST['password']);
			if($this->_model->validateCredentials($creds)) {
				API::Redirect("/");
			} else {
				API::Error("Invalid Username/Passord for login.");
			}
		}
		$this->addModuleTemplate('auth', 'login_frm');

	}

	public function logoutAction() {
		session_destroy();
		API::Redirect("/");
	}

    	public function delete_confirmAction() {
    		$this->_view->data = $this->_model->get($this->req_id);
    		$this->addModuleTemplate($this->module, 'delete_confirm');
	}
    	

    /**
	* default delete action pretty straight forward
	*
	* @return none
	*/

    public function deleteAction() {
	if(!isset($this->params['cancel'])) {
		if($this->_model->name == "admin") {
			API::Message("You cannot delete 'admin'");
		} else {	
			// XXX: Maybe do some hook call validation here?
			
			// auto call the hooks for this module/action
    			API::callHooks(self::$module, $this->action, 'controller', $this->req_id);
	
		    	// delete an entry
        		$host = $this->_model->delete();
		}
	}
        API::redirect(API::printUrl($this->_redirect));
    }

}
?>

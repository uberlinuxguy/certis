<?
/**
* This is the class to instantiate an Auth_LDAPController object
* for the Auth_LDAP module.  Set the 'auth_class' variable in the
* config file to use the 'Auth_LDAP' module to use this code. This
* does not extend DefaultController because it doesn't need any of the
* default behavior.
*
* @package Modules
* @subpackage Auth
* @author Jason <uberlinuxguy@tulg.org>
*/
class Auth_LDAPController extends Controller {

    public function __construct() {
        parent::__construct();
        # initialize the auth controller
        $this->req_id = (isset($this->req_id)) ? $this->req_id : '';

        $this->_model = new Auth_LDAP();
    }

	public function authCheckAction() {
		API::DEBUG("[Auth_LDAPController::authCheckAction] In Function", 8);
		if(!isset($this->authed_user) || $this->authed_user == 0) {
			API::DEBUG("[Auth_LDAPController::authCheckAction] authed_user is not " . $this->authed_user, 8);
			API::Redirect(API::printUrl(strtolower(self::$config->auth_class), 'login'));
		} else {
			API::DEBUG("[Auth_LDAPController::authCheckAction] validating authed_user as " . $this->authed_user, 8);
			if(!$this->_model->validateUID($this->authed_user)) {
				API::Redirect(API::printUrl(strtolower(self::$config->auth_class), 'login'));
			}
		}
	}

	public function loginAction() {
		if(isset($_POST['login'])) {
			$creds = array();
			$creds['uname'] = $_POST['uname'];
			$creds['password'] = $_POST['password'];
			if($this->_model->validateCredentials($creds)) {
				API::DEBUG("[Auth_LDAPController::loginAction] PHPSESSID = " . session_id(),8 );
				API::Redirect("/");
			} else {
				API::Error("Invalid Username/Password");
			}
		}
		API::DEBUG("[Auth_LDAPController::loginAction] adding login form to template stack");
		$this->addModuleTemplate(strtolower(self::$config->auth_class), 'login_frm');

	}

	public function logoutAction() {
		session_destroy();
		API::Redirect("/");
	}


}
?>

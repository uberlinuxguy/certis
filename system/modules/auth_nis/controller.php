<?
/**
* This is the class to instantiate an Auth_NISController object
* for the Auth_NIS module.  Set the 'auth_class' variable in the
* config file to use the 'Auth_NIS' module to use this code. This
* does not extend DefaultController because it doesn't need any of the
* default behavior.
*
* @package Modules
* @subpackage Auth
* @author Jason <uberlinuxguy@tulg.org>
*/
class Auth_NISController extends Controller {

    public function __construct() {
        parent::__construct();
        # initialize the auth controller
        $this->req_id = (isset($this->req_id)) ? $this->req_id : '';

        $this->_model = new Auth_NIS();
    }

	public function authCheckAction() {
		API::DEBUG("[Auth_NISController::authCheckAction] In Function", 8);
		if(!isset($this->authed_user) || $this->authed_user == 0) {
			API::DEBUG("[Auth_NISController::authCheckAction] authed_user is not " . $this->authed_user, 8);
			API::Redirect(API::printUrl(strtolower(self::$config->auth_class), 'login'));
		} else {
			API::DEBUG("[Auth_NISController::authCheckAction] validating authed_user as " . $this->authed_user, 8);
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
				API::DEBUG("[Auth_NISController::loginAction] PHPSESSID = " . session_id(),8 );
				API::Redirect("/");
			} else {
				API::Error("Invalid Username/Password");
			}
		}
		API::DEBUG("[Auth_NISController::loginAction] adding login form to template stack");
		$this->addModuleTemplate(strtolower(self::$config->auth_class), 'login_frm');

	}

	public function logoutAction() {
		session_destroy();
		API::Redirect("/");
	}


}
?>

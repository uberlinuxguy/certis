<?php
/**
* This is the class to instantiate a PermsController object
* for Perms module
*
* @package Modules
* @subpackage Perms
* @author Jason <uberlinuxguy@tulg.org>
*/
class PermsController extends DefaultController {


    public function __construct() {
        parent::__construct();
        # initialize the Perms controller
        $this->_model = new Perms();
		$this->activeSection='perms';
    }

	public function indexAction() {
		$this->addModuleTemplate($this->module, 'index');
	}
	public function selectUserAction() {
		$auth_class_name = self::$config->auth_class;
		$auth = new $auth_class_name;
		$this->_view->data['users'] = $auth->listUsers();
		$this->addModuleTemplate($this->module, 'selectUser');
	}
	
	public function displayAction() {
		
		$auth_class_name = self::$config->auth_class;
		$auth = new $auth_class_name;
		if(isset($this->params['uid']) && is_numeric($this->params['uid'])) {
				$this->_view->data['info'] = $auth->getUserByUID($this->params['uid']);
		} else {
			$this->_view->data['info'] = $auth->getUserByName($this->params['user']);
		}
		$this->_view->data['perms']=$this->CertisInst->Perms->getAllPerms();
		$this->_view->data['action'] = 'edit'; 
		$this->addModuleTemplate($this->module, 'display');	
	
	}
	public function validatePerms(){ 
		// all sections of the perms module require the perms_admin permission
		
		return $this->CertisInst->Perms->checkPerm($this->authed_user, "perms_admin");
    }
}

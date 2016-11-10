<?php
/**
* This is the class to instantiate a PrefsController object
* for Prefs module
*
* @package Modules
* @subpackage Prefs
* @author Jason <uberlinuxguy@tulg.org>
*/
class PrefsController extends DefaultController {


    public function __construct() {
        parent::__construct();
        # initialize the Prefs controller
        /*
        $this->params['uid'] = (isset($this->params['uid'])) ? $this->params['uid'] : '';

        $this->_model = new Prefs((is_numeric($this->params['uid']) ? $this->params['uid'] : ''));
		*/
        $this->_model = new Prefs();
        $this->activeSection='prefs';
    }
    
    public function indexAction() {
    	
    	$tmp_info =  $this->_model->get($this->authed_user, 'uid');
    	unset($tmp_info->perms);
    	$this->_view->data['info'] = $tmp_info; 
    	API::callHooks($this->module, $this->action, 'controller', $this->_view->data);
    	$this->addModuleTemplate($this->module, "index");
    	
    	
    }
    
    public function validatePerms(){
    	API::DEBUG("[Prefs::validatePerms()] Action is ". $this->action, 8);
    	// validate permissions for the prefs module.
    	switch ($this->action) {
    		case 'index':
    			// index action is always allowed because it will only pull information
    			// for the currently authenticated user, which is 'safe'
    			return TRUE;
    		default:
    			// if the current user is trying to save info for a different user 
    			// other than themselves, check their permissions to do so.
    			if($this->authed_user != $this->params['uid']) {
    				return $this->CertisInst->Perms->checkPerm($this->authed_user, "user_admin");
    			} else {
    				return TRUE;
    			
    			}
    	} 
    }

}

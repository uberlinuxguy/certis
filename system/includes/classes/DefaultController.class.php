<?php

/**
 * This is a class that implements defaults functions that a module might
 * use.  Usage of this class is not required, and controllers can implement
 * the Controller class directly to get a clean slate.
 *
 * @package System
 * @author Jason <uberlinuxguy@tulg.org>
 */

class DefaultController extends Controller {

	protected $_redirect = "/";

	public function __construct() {
		if(!$this->validatePerms()){
			API::Error("You do not have permissions to '" . $_SERVER['REQUEST_URI'] . "' section.");
			API::Redirect("/");
		}
		$this->_redirect = self::$module;
		parent::__construct();
	}
	public function secCheck(){
		$permChk = new Perms();
		if($permChk->checkPerm($this->authed_user, $this->module)){
			return true;
		}
		return false;
	
	}

	/**
	 * default function to check permissions.  Called by DefaultController Constructor.  Override 
	 * this if you want permissions to be checked for certain actions.  By default, permissions are
	 * granted to everything.  
	 * @return bool
	 */
	public function validatePerms(){ 
		return TRUE;
	}

	/**
	 * default action for an index page that consists of rows of information.  Implement using
	 * your own custom index.php template
	 *
	 * @return none
	 */
	public function indexAction() {
		$this->_model->where_clause(NULL);

		if(!isset($this->params['page'])) {
			self::$params['page'] = 1;
		} 

		$this->_view->data['info'] = $this->_model->getPage($this->params['page'], $this->config->pagination_limit);
		$this->_view->data['total_items'] = $this->_model->getCount();
		$this->addModuleTemplate($this->module, 'index');

	}

	/**
	 * default action for displaying information for a single element in
	 * a table. Implement using  your own custom display.php and/or
	 * edit.php template.
	 * NOTE: This function DOES NOT resolve inter-table dependant fields.
	 * Override this function if you need to do that.
	 *
	 * @return none
	 */
	public function displayAction() {
		if(is_numeric($this->req_id)) {
			API::DEBUG("[DefaultController::displayAction()] Getting " . $this->req_id, 1);
			$this->_view->data['info'] = $this->_model->get($this->req_id);
			$this->_view->data['action'] = 'edit';

		} else {
			$this->_view->data['action'] = 'new';
		}
		// auto call the hooks for this module/action
		error_log("Should Call: " . $this->module ."/".$this->action."/"."controller.php");
		API::callHooks($this->module, $this->action, 'controller', $this);

		$this->addModuleTemplate($this->module, 'display');

	}

	/**
	 * default action processing edit actions on this table.  Does not implement a template.
	 * Uses the 'set_data' function on the model object of the implementing class to do data
	 * verification.
	 *
	 * @return none
	 */
	public function editAction() {
# process the edit form.
# check the post data and filter it.
		if(isset($_POST['cancel'])) {
			API::Redirect(API::printUrl($this->_redirect));
		}
		$input_check = $this->_model->check_input($_POST);
		if(is_array($input_check)) {
			API::Error($input_check);
			API::redirect(API::printUrl($this->_redirect));
		}
		// all hooks will stack their errors onto the API::Error stack
		// but WILL NOT redirect.
		API::callHooks(self::$module, 'validate', 'controller', $_POST);
		if(API::hasErrors()) {
			API::redirect(API::printUrl($this->_redirect));
		}

		$this->_model->set_data($_POST);

		// auto call the hooks for this module/action
		API::callHooks(self::$module, 'save', 'controller');

		API::redirect(API::printUrl($this->_redirect));


	}

	/**
	 * default delete action pretty straight forward
	 *
	 * @return none
	 */

	public function deleteAction() {
		if(!isset($this->params['cancel'])) {

			// XXX: Maybe do some hook call validation here?

			// auto call the hooks for this module/action
			API::callHooks(self::$module, $this->action, 'controller', $this);

			// delete an entry
			$host = $this->_model->delete();
		}
		API::redirect(API::printUrl($this->_redirect));
	}

	/**
	 * default action processing new requests passed in from the display action.  Does
	 * not use a template. Uses the 'set_data' function on the model object of the implementing
	 * class to do data verification.
	 *
	 * @return none
	 */
	public function newAction() {
# process the new entry form.
# check the post data and filter it.
		if(isset($_POST['cancel'])) {
			API::Redirect(API::printUrl($this->_redirect));
		}
		$input_check = $this->_model->check_input($_POST);
		if(is_array($input_check)) {
			API::Error($input_check);

			// redirect to index and displayed an error there.
			API::redirect(API::printUrl($this->_redirect));
		}

		// all hooks will stack their errors onto the API::Error stack
		// but WILL NOT redirect.
		API::callHooks(self::$module, 'validate', 'controller', $_POST);
		if(API::hasErrors()) {
			API::redirect(API::printUrl($this->_redirect));
		}

		// set the id into the post var for any hooks.
		$_POST['id'] = $this->_model->set_data($_POST, TRUE);

		// auto call the hooks for this module/action
		API::callHooks(self::$module, 'save', 'controller', $_POST);
		if(isset($this->params['redir'])) {
			API::Redirect($this->params['redir']);
		}
		API::redirect(API::printUrl($this->_redirect));
	}



}

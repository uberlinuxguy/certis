<?php
/**
* This is the class to instantiate a HostsController object
* for Hosts module
*
* @package Modules
* @subpackage Hosts
* @author Jason <uberlinuxguy@tulg.org>
*/
class HostsController extends DefaultController {


    public function __construct() {
        parent::__construct();
        # initialize the hosts controller
        $this->req_id = (isset($this->req_id)) ? $this->req_id : '';

        $this->_model = new Hosts((is_numeric($this->req_id) ? $this->req_id : NULL));
        $this->activeSection='hosts';
    }
    
    public function delete_confirmAction() {
    	$this->_view->data = $this->_model->get($this->req_id);
    	$this->addModuleTemplate($this->module, 'delete_confirm');
    	
    }
    


}

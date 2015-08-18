<?php
/**
* This is the class to instantiate a MainController object.
* This is the controller that handles requests where no module is specified.
*
* @package System
* @author Jason <uberlinuxguy@tulg.org>
*/
class MainController extends Controller {

	public function indexAction() {
		$this->addTemplate(_SYSTEM_.'/templates/' . $this->action . ".php");
	}
	
	public function errorAction() {
		$this->addTemplate(_SYSTEM_ . '/templates/' . $this->action . ".php");
	}
}
?>

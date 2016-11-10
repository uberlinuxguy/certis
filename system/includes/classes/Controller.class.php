<?php
/**
* This is the base Controller class, implementing some
* of the most basic shared functions across controllers
* and instantiating a View class on the object.
*
* Implementers of this Class should have a call to
* parent::__construct() at the beginning of their
* custom __construct() class in order to instantiate
* the View class properly.
*
* @package System
* @author Jason <uberlinuxguy@tulg.org>
*/
class Controller extends Certis {

    protected $_model = NULL;
    protected $_view = NULL;


    public function __construct() {

        $this->_view = new View;


    }

	/**
	* Pass-thru for the View::displayPage() function
	*
	* @access	public
	*/
    public function displayPage() {
        $this->_view->displayPage();

    }

	/**
	* Pass-thru for the View::addTemplate() function
	*
	* @access	public
	*/
    public function addTemplate($template) {

        $this->_view->addTemplate($template);

    }

	/**
	* This calls addTemplate() on the View class adding a template specific
	* to a module.
	*
	* @access	public
	*/
    public function addModuleTemplate($module, $template) {

        $this->_view->addTemplate(_SYSTEM_ . "/modules/" . $module . "/templates/" . $template . ".php");

    }
}

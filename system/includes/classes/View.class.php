<?php
/**
* This is View class used to create the HTML sent back to the browser
*
* @package System
* @author Jason <uberlinuxguy@tulg.org>
*/
class View extends Certis {


    protected $_templates = array();

    protected $_ajax = FALSE;

    public $data = NULL;

	/**
	* display the full paqge, adding all templates and running their code.
	*
	* @return none
	*/
    public function displayPage() {


        $data = $this->data;
        $view = $this;

        foreach($this->_templates as $template) {
			if(file_exists($template)) {
            	include $template;
			} else {
				error_log("[System::View::displayPage()]: Unable to find template $template");
			}
        }


    }

	/**
	* safely get a value from a single row that is returned in the 'info' element of data
	*
	* @return mixed
	*/
    public function getInfoValue($name) {
    	if(isset($this->data['info'])) {
			if(is_object($this->data['info'])) {
				if(isset($this->data['info']->{$name})) {
					return $this->data['info']->{$name};
				}
			} elseif (is_array($this->data['info'])) {
				if(isset($this->data['info'][$name])) {
					return $this->data['info'][$name];
				}
			}
		}
    }

	/**
	* add a template to the stack of templates to display for this page.
	*
	* @param string $template the full path to the template to add
	* @return none
	*/
    public function addTemplate($template) {
        $this->_templates[] = $template;

    }

	/**
	* used in templates to print pagination for the current result set
	* based on the 'pagination_limit' configuration variable.
	*
	* @return none
	*/
    public function printPages() {

    	$total_items = $this->data['total_items'];
    	$items_per_page = $this->config->pagination_limit;
    	$current_page = $this->params['page'];

    	include _SYSTEM_ . '/templates/page_line.inc.php';
    }

}

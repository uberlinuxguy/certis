<?php
/**
 * index.php - this file contains the main running script for Certis
 *
 */


// Pull in the main initialzation script which will fire off Certis->initCertis() 
require_once('../system/includes/system_init.php');

API::DEBUG("[__SYSTEM__]  index.php: about to check authentication...");
// check authentication if the module requested is not an authless module.
if(!in_array($CertisInst->module, $CertisInst->authless)) {
	// only run this code if the class specified in config
	// for authentication is present.  And if the request
	// coming in is not for the auth_class module itself.
	// The second part keeps things from going crazy.
	if(class_exists($CertisInst->config->auth_class) && $CertisInst->module != strtolower($CertisInst->config->auth_class) ) {

		// include the authentication module's controller.php file
		include _SYSTEM_ . "/modules/" . strtolower($CertisInst->config->auth_class) . "/controller.php";

		// instantiate the auth controller class
		$classname = $CertisInst->config->auth_class . "Controller";
		$auth_controller = new $classname;
		$auth_controller->authCheckAction();
	}
	if(!class_exists($CertisInst->config->auth_class)) {
		$CertisInst->module = '';
		$CertisInst->action = 'error';
		API::Error('FATAL ERROR: Unable to find Authentication Class');
		
	}
}
API::DEBUG("[__SYSTEM__] index.php: authentication check done.");

$controller = null;


if (!empty($CertisInst->module)){
	if(preg_match("/\.\./",$CertisInst->module)){
		error_log("[index.php] FATAL ERROR! SOMEONE TRIED TO ESCAPE! ". $CertisInst->module);
		print ("UNAUTHORIZED!!!!!!");
		exit(1);
	}
	
	// first check to see if the module exists.
	if(!file_exists(_SYSTEM_ . "/modules/" . $CertisInst->module)){ 
			error_log("[index.php] Unable to find requested module: " . $CertisInst->module);
			API::Redirect("/");
	}
	
    // use this module's controller
    // to create a new instance of it's controller to work with for
    // this request.
    $classname = ucfirst($CertisInst->module). "Controller";
} else {
	// if no module is specified, pull in the main system controller and
	// set it to be the new intantiated class
    $classname = "MainController";
    if($CertisInst->action == 'error') {
    	// unset the leftNav var so no nav links are displayed
		unset($GLOBALS['leftNav']);
    }
}
// now we know what controller to bring in.
$controller = new $classname;

// add the header to the top of the stack of templates if this is not an ajax request
if (!(isset($CertisInst->params['ajax']))) {
    $controller->addTemplate(_SYSTEM_ . '/templates/header.inc.php');
}

// call the action function on the controller, this may or may not add more templates onto the stack.
$controller->{$CertisInst->action . "Action"}();

// finally call the footer template in at the bottom, if this is not an ajax request.
if (!(isset($CertisInst->params['ajax']))) {
    $controller->addTemplate(_SYSTEM_ . '/templates/footer.inc.php');
}

// finally, display the page.  This runs all the templating code
// by passing everything over to the _view object on the controller object.
$controller->displayPage();

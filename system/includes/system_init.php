<?php
// TODO: rename this file to system_init.php
// TODO: Merge most of this into the Certis base class initCertis() method.




// A macro to define the system directory
define('_SYSTEM_', dirname(__FILE__) . '/..');


// Include main configuration file
require_once('../system/config/config.php');

// XXX: Autoload needs to be here, and should really be the FIRST thing in this file.  
// that or we can put it into another include to make init.php look nicer
// set up the function to auto load classes
function classLoader($class_name) {
	if(preg_match("/\.\./",$class_name)){
		error_log("[System::__autoload] FATAL ERROR! SOMEONE TRIED TO ESCAPE! $class_name");
		exit(1);
	}
	if($class_name == "MainController") {
		// if the class we are looking for is MainController, then include the
		// controller.php from system/
		require_once(_SYSTEM_ . "/controller.php");
		return;
	}

	// see if it's a system level class
	if(file_exists(_SYSTEM_ . "/includes/classes/" . $class_name . ".class.php")) {
		require_once(_SYSTEM_ . "/includes/classes/" . $class_name . ".class.php");
		return;
	} else {
		// nope not a system class, time to try to find it in the modules.
		if(preg_match("/Controller/", $class_name)) {
			// if it's a controller class being requested, remove controller, lower the resulting
			// string and that should be the module name
			$module = strtolower(preg_replace("/Controller/", "", $class_name));
			require_once(_SYSTEM_ . "/modules/$module/controller.php");
			return;
		} else {
			// otherwise, it's a Model from within a module somewhere.
			$class_file = glob(_SYSTEM_ . "/modules/*/" . $class_name . ".model.php");
			if(count($class_file) == 1 ) {
				require_once($class_file[0]);
				return;
			} elseif(count($class_file) > 1) {
				error_log("[System::__autoload] Ambiguous Class $class_name. Multiple Class files found.  Not Autoloading.");
			} else {
				error_log("[System::__autoload] $class_name not found.");
			}
		}
	}
}
if(!spl_autoload_register('classLoader', true, true)){
	error_log("[System::init()] Unable to register classLoader");
	exit(1);
}

// create a new instance of the base Certis class.  
$CertisInst = new Certis;

// ... and call the initCertis() function to set 
// the object environment for all extended objects from here on.
$CertisInst->initCertis();

# Set out timezone
date_default_timezone_set($CertisInst->config->time_zone);





?>

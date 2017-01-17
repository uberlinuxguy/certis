<?php


	// add the logut menu if authed_user is set
	API::DEBUG("[Auth_NIS] init.php: authed_user = ". $this->authed_user);
	if(isset($this->authed_user) && $this->config->auth_class=="Auth_NIS") {
		API::addMenuItem(9999, 'Logout', 'auth_nis', 'logout');
	}



<?php


	// add the logut menu if authed_user is set
	API::DEBUG("[Auth_LDAP] init.php: authed_user = ". $this->authed_user);
	API::DEBUG("[Auth_LDAP] init.php: auth_class = ". $this->config->auth_class, 1);
	if(isset($this->authed_user) && $this->config->auth_class=="Auth_LDAP") {
		API::addMenuItem(9999, 'Logout', 'auth_ldap', 'logout');
	}



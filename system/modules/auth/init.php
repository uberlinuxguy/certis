<?

	// permission to create internal users.
	$this->Perms->registerPerm('auth_internal_admin', 'Internal User Managment');
	if($this->Perms->checkPerm($this->authed_user, 'auth_internal_admin') && $this->config->auth_class == "Auth") {
		API::addMenuItem(89, 'Internal User Managment', 'auth');
	}

	// add the logut menu if authed_user is set
	if(isset($this->authed_user)) {
		API::addMenuItem(9999, 'Logout', 'auth', 'logout');
	}



<?php
	
	// add the permissions managment option to the left nav if the logged in user 
	// is a sysadmin
	$this->Perms->registerPerm('perms_admin', 'Permissions Management');
	if($this->Perms->checkPerm($this->authed_user, 'perms_admin')) {
		API::addMenuItem(90, 'Permissions Management', 'perms');
	}
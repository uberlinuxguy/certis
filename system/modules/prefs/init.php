<?
	$this->Perms->registerPerm('user_admin', 'User Management');
	if(!empty($this->authed_user)){
		API::addMenuItem(91, 'My Preferences', 'prefs');	
	}
	
<?
	// can't use globals stuff here, because it's init.php which happens out of order
	// of other init.php's so declare and destroy our own version of the Perms object.
	$tmp_perms = new Perms();
	$tmp_perms->registerPerm('hosts', 'Host Admin');

	if($tmp_perms->checkPerm($this->authed_user, 'hosts')) {
		// register a menu item for this module
		API::addMenuItem(11, "Host Admin", "hosts");
	}
	unset($tmp_perms);

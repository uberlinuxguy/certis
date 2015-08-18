<?
	//TODO: Check prefs permissions and maybe check that we are setting only our prefs unless admin
	$prefs = new Prefs();
	if(is_numeric($_POST['id'])) {
		$_POST['uid'] = $_POST['id'];
	}
	$insert_data=FALSE;
	if($_POST['action'] == "new") {
		$insert_data=TRUE;
	}
		
	$prefs->set_data($_POST,$insert_data);


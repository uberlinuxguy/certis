<?
	if(is_numeric($data)) {
		$prefs = new Prefs($data);
		$prefs->delete();
		unset($prefs);
		unset($pref_data);
	} 

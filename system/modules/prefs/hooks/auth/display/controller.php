<?php
	if(is_numeric($data->req_id)) {
		$prefs = new Prefs();
		$pref_data = $prefs->get($data->req_id,"uid");
		$GLOBALS['hooks']['auth']['prefs'] = $pref_data;
		unset($prefs);
		unset($pref_data);
	} else {
		$GLOBALS['hooks']['auth']['prefs']->fname="";
		$GLOBALS['hooks']['auth']['prefs']->lname="";
	}

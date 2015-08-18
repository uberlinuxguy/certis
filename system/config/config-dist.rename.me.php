<?php

// config is handled via an object.
$conf = new StdClass();

// Define the base URL
$conf->URL = 'http://localhost/';

// should we be using 'Pretty Urls'?
$conf->pretty_urls = TRUE;

// Our time zone, handy.
$conf->time_zone = "America/New_York";

// what will the session cookie be.  Mmmm me like cookies.
$conf->session_cookie = "localhost";

// The class that will handle authentication.  Most Likely supply by
// a module.
$conf->auth_class = 'Auth';

// The class to handle sessions.  Unset this to use the default PHP stuff.
$conf->session_class = "SessionDB";

// The default lifetime of a session in seconds
$conf->session_lifetime = 1800; // half hour

// pagination limit, generally speaking.
$conf->pagination_limit = 10;

// set to true for debug messages
$conf->debug = 0;

// what do we call ourselves.  You will want to adjust this.
$conf->appname = "*** CHANGE ME ***";

// should we auto-create user prefs entries?
$conf->prefs_auto = TRUE;

// db connection setup
$conf->db = new StdClass();
$conf->db->dbuser = '';
$conf->db->dbpass = '';
$conf->db->dbname = 'certis';
$conf->db->dbtype = 'mysql';
$conf->db->writehost = '127.0.0.1';
$conf->db->readhost = $conf->db->writehost;
$conf->db->dbtrans = FALSE;

// glob in module config files
foreach (glob(_SYSTEM_ . "/config/modules/*.config.php") as $config_file) {
	include($config_file);
}


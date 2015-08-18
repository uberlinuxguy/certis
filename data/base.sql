DROP TABLE IF EXISTS `prefs`;
CREATE TABLE `prefs` (
  `uid` int(10) unsigned NOT NULL,
  `fname` varchar(255) NOT NULL default '',
  `lname` varchar(255) NOT NULL default '',
  `perms` int(11) NOT NULL default '0',
  `auth_mod` varchar(64) NOT NULL default 'auth',
  PRIMARY KEY (`uid`)
);

DROP TABLE IF EXISTS `perms`;
CREATE TABLE `perms` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `descr` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
);

DROP TABLE IF EXISTS `auth`;
CREATE TABLE `auth` (
  `uid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL default '',
  PRIMARY KEY (`uid`)
);

INSERT INTO `auth` VALUES (7,'admin','21232f297a57a5a743894a0e4a801fc3');
INSERT INTO `prefs` VALUES (7, 'System', 'Administrator', -1, 'auth');


DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
	`sid` varchar(100) NOT NULL,
	`data` text NULL,
	`mod_time` TIMESTAMP NOT NULL,
	PRIMARY KEY(`sid`)
);

CREATE TABLE `hosts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `alias` varchar(255) default NULL,
  `location` varchar(128) NOT NULL default 'N/A',
  `nagios_link` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;
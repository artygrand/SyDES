CREATE TABLE `PREFIX_pages` (
	`id` int(11) unsigned NOT NULL auto_increment,
	`parent_id` int(11) unsigned default NULL,
	`alias` varchar(150) default NULL,
	`fullpath` TEXT,
	`status` tinyint(1) NOT NULL default '1',
	`template` varchar(100) NOT NULL,
	`type` varchar(50) NOT NULL,
	PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_pages_content` (
	`page_id` int(11) NOT NULL,
	`locale` varchar(50) NOT NULL,
	`title` varchar(500) default NULL,
	`content` TEXT,
	UNIQUE KEY (page_id,locale)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_pages_meta` (
	`id` int(11) unsigned NOT NULL auto_increment,
	`page_id` int(11) NOT NULL,
	`key` TEXT,
	`value` TEXT,
	PRIMARY KEY (id),
	UNIQUE KEY (page_id,key)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_page_types` (
	`pos` int(11) unsigned NOT NULL auto_increment,
	`type` varchar(50) NOT NULL,
	`name` varchar(255) NOT NULL,
	`dflt_tpl` varchar(100) NOT NULL,
	PRIMARY KEY (pos),
	UNIQUE KEY (type,name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `PREFIX_config_meta` (
	`id` int(11) unsigned NOT NULL auto_increment,
	`page_id` int(11) NOT NULL,
	`key` TEXT,
	`value` TEXT,
	PRIMARY KEY (id),
	UNIQUE KEY (page_id,key)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE PREFIX_access (
	`id` int(11) unsigned NOT NULL auto_increment,
	`date` datetime NOT NULL,
	`ip` TEXT default NULL,
	`login` TEXT default NULL,
	`password` TEXT default NULL,
	`text` TEXT default NULL,
	PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
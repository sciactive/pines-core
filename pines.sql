SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `pin_com_entity_data` (
  `id` bigint(20) NOT NULL auto_increment,
  `guid` bigint(20) NOT NULL,
  `name` text NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id_guid` (`guid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `pin_com_entity_entities` (
  `guid` bigint(20) NOT NULL auto_increment,
  `parent` bigint(20) default NULL,
  `tags` text,
  PRIMARY KEY  (`guid`),
  KEY `id_parent` (`parent`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
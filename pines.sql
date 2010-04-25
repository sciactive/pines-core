SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/* CREATE USER 'pines'@'localhost' IDENTIFIED BY 'password';

GRANT USAGE ON *.* TO 'pines'@'localhost'
WITH
	MAX_QUERIES_PER_HOUR 0
	MAX_CONNECTIONS_PER_HOUR 0
	MAX_UPDATES_PER_HOUR 0
	MAX_USER_CONNECTIONS 0; */

CREATE DATABASE IF NOT EXISTS `pines`;

USE `pines`;

/* GRANT ALL PRIVILEGES ON `pines`.* TO 'pines'@'localhost'; */

CREATE TABLE IF NOT EXISTS `pin_com_myentity_data` (
  `guid` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY (`guid`,`name`(330))
) ;

CREATE TABLE IF NOT EXISTS `pin_com_myentity_entities` (
  `guid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tags` text,
  `varlist` text,
  PRIMARY KEY (`guid`),
  KEY `id_tags` (`tags`(1000)),
  KEY `id_varlist` (`varlist`(1000))
) AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `pin_com_myentity_uids` (
  `name` text NOT NULL,
  `cur_uid` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`name`(100))
) ;
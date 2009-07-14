-- phpMyAdmin SQL Dump
-- version 3.1.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 06, 2009 at 07:26 PM
-- Server version: 5.0.75
-- PHP Version: 5.2.6-3ubuntu4.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `dandelion`
--

-- --------------------------------------------------------

--
-- Table structure for table `ddl_com_entity_data`
--

CREATE TABLE IF NOT EXISTS `ddl_com_entity_data` (
  `id` bigint(20) NOT NULL auto_increment,
  `guid` bigint(20) NOT NULL,
  `name` text NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `ddl_com_entity_data`
--

INSERT INTO `ddl_com_entity_data` (`id`, `guid`, `name`, `value`) VALUES
(4, 1, 'password', 's:32:"47759709c486c8f54840dd5e80732901";'),
(3, 1, 'salt', 's:32:"ef47ba7ac0f5631b7bd5eaf4a8bcc648";'),
(2, 1, 'username', 's:5:"admin";'),
(0, 1, 'email', 'N;'),
(1, 1, 'abilities', 'a:1:{i:0;s:10:"system/all";}');

-- --------------------------------------------------------

--
-- Table structure for table `ddl_com_entity_entities`
--

CREATE TABLE IF NOT EXISTS `ddl_com_entity_entities` (
  `guid` bigint(20) NOT NULL auto_increment,
  `parent` bigint(20) default NULL,
  `tags` text,
  PRIMARY KEY  (`guid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `ddl_com_entity_entities`
--

INSERT INTO `ddl_com_entity_entities` (`guid`, `name`, `parent`, `tags`) VALUES
(1, '', NULL, 'a:2:{i:0;s:8:"com_user";i:1;s:4:"user";}');
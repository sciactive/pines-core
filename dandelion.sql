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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `ddl_com_entity_entities`
--

CREATE TABLE IF NOT EXISTS `ddl_com_entity_entities` (
  `guid` bigint(20) NOT NULL auto_increment,
  `parent` bigint(20) default NULL,
  `tags` text,
  PRIMARY KEY  (`guid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
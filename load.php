<?php
/**
 * Pines' loader.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * The main configuration object for Pines.
 *
 * This object is used to hold everything from Pines settings, to component
 * unctions. Components should put their own classes under $config, using their
 * components name. For example, a component named com_xmlparser should put its
 * class in $config->com_xmlparser. Though, it is not necessary to do this.
 * $config also holds Pines' standard classes, which include:
 *
 * hook - Hooking system. (Part of the base system.)
 *
 * configurator - Manages Pines configuration.
 *
 * log_manager - Manages logging features.
 * 
 * entity_manager - Manages entities.
 *
 * db_manager - Manages database connections.
 *
 * user_manager - Manages users.
 *
 * ability_manager - Manages users' abilities.
 *
 * @global DynamicConfig $config
 */
$config = new DynamicConfig;

/**
 * The hooking system.
 *
 * @global hook $config->hook
 */
$config->hook = new hook;

?>

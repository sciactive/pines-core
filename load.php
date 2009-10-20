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
 * This object is used to hold everything from Pines' settings, to component
 * functions. Components' configure.php files will be parsed into $config under
 * the name of their component. Such as $config->com_xmlparser. Components
 * should put their own classes under $config, using their name with run_
 * instead of com_. For example, com_xmlparser should put its class in
 * $config->run_xmlparser, though it is not strictly necessary to do this.
 *
 * $config also holds Pines' standard classes, which include:
 *
 * - hook - Hooking system. (Part of the base system.)
 * - depend - Dependency checker. (Part of the base system.)
 * - configurator - Manages Pines configuration.
 * - log_manager - Manages logging features.
 * - entity_manager - Manages entities.
 * - db_manager - Manages database connections.
 * - user_manager - Manages users.
 * - ability_manager - Manages users' abilities.
 *
 * @global dynamic_config $config
 */
$config = new dynamic_config;

/**
 * The hooking system.
 *
 * @global hook $config->hook
 */
$config->hook = new hook;

/**
 * The dependency checker.
 *
 * @global depend $config->depend
 */
$config->depend = new depend;

?>

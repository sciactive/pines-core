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
 * the name of their component. Such as $config->com_xmlparser. Components'
 * classes will be automatically loaded into $config under their name with run_
 * instead of com_ when the variable is first used. For example, com_xmlparser
 * will be loaded the first time $config->run_xmlparser is accessed.
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
 * - editor - Provides a content editor.
 *
 * When you want your component to be one of Pines' standard classes, place a
 * string with the name of your component's class into the appropriate variable.
 *
 * For example, if you are designing a log manager called com_email_logs, use
 * this in your load.php file:
 *
 * $config->log_manager = 'com_email_logs';
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

/**
 * The display manager.
 * 
 * @global page $config->page
 */
$config->page = new page;

?>

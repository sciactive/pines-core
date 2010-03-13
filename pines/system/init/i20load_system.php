<?php
/**
 * Load the system classes and objects.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

// Strip magic quotes.
if (get_magic_quotes_gpc()) {
	pines_stripslashes_array_recursive($_GET);
	pines_stripslashes_array_recursive($_POST);
	pines_stripslashes_array_recursive($_REQUEST);
	pines_stripslashes_array_recursive($_COOKIE);
	if (P_SCRIPT_TIMING) pines_print_time('Strip Request Slashes');
}

// Load system classes.
$temp_classes = pines_scandir("system/classes/");
foreach ($temp_classes as $cur_class) {
	/**
	 * Include each system class.
	 */
	include_once("system/classes/$cur_class");
}
unset($temp_classes);
if (P_SCRIPT_TIMING) pines_print_time('Load System Classes');

/**
 * The main object for Pines.
 *
 * This object is used to hold everything from Pines' settings, to component
 * functions. Components' configure.php files will be parsed into $pines->config
 * under the name of their component. Such as $pines->config->com_xmlparser.
 * Components' classes will be automatically loaded into $pines under their name
 * when the variable is first used. For example, com_xmlparser will be loaded
 * the first time $pines->com_xmlparser is accessed.
 *
 * $pines also holds Pines' standard classes/objects, which include:
 *
 * - config - Configuration. (Part of the base system.)
 * - hook - Hooking system. (Part of the base system.)
 * - depend - Dependency checker. (Part of the base system.)
 * - template - The current template's object.
 * - configurator - Manages Pines' and components' configuration.
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
 * $pines->log_manager = 'com_email_logs';
 *
 * @global dynamic_loader $pines
 */
$pines = new dynamic_loader;

/**
 * Pines' and components' configuration.
 *
 * @global dynamic_config $pines->config
 */
$pines->config = new dynamic_config;

/**
 * The hooking system.
 *
 * @global hook $pines->hook
 */
$pines->hook = new hook;

/**
 * The dependency checker.
 *
 * @global depend $pines->depend
 */
$pines->depend = new depend;

/**
 * The menu system.
 *
 * @global menu $pines->menu
 */
$pines->menu = new menu;

/**
 * The display manager.
 *
 * @global page $pines->page
 */
$pines->page = new page;

?>
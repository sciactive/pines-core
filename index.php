<?php
/**
 * The controller for Pines' architecture.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */

/*
 * Pines - a Lightweight PHP Application Framework
 * Copyright (C) 2008-2009  Hunter Perrin.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Hunter can be contacted at hunter@sciactive.com
 *
 */

/**
 * Constants
 */
/**
 * Declare that the program is running through the index, as it should.
 */
define('P_RUN', true);
/**
 * The installation's base path.
 */
define('P_BASE_PATH', dirname(__FILE__));
/**
 * The name of our index file.
 */
define('P_INDEX', basename($_SERVER['SCRIPT_FILENAME']));
/**
 * The microtime when the script started executing.
 */
define('P_EXEC_TIME', microtime(true));
/**
 * When this is set to true, the times between script stages will be printed.
 */
define('P_SCRIPT_TIMING', false);

if (P_SCRIPT_TIMING) {
	ob_start();
	/**
	 * Print a message for Pines Script Timing.
	 *
	 * @param string $message The message.
	 * @param bool $print_now Whether to print the page now.
	 */
	function pines_print_time($message, $print_now = false) {
		static $time_output;
		static $script_output;
		$script_output .= ob_get_contents() or '';
		ob_clean();
		$microtime = microtime(true);
		$time_output .= sprintf(str_pad($message, 30).'%F<br />', ($microtime - $GLOBALS['p_last_time']));
		$GLOBALS['p_last_time'] = $microtime;
		if ($print_now) {
			echo '<h1>Pines Script Timing</h1><p>Times are measured in seconds.</p><pre>';
			echo $time_output;
			printf('--------------------<br />'.str_pad('Script Run', 30).'%F</pre><br />', ($microtime - P_EXEC_TIME));
			ob_flush();
			echo 'Here is the page\'s output (it may have been mangled during rendering):<br />';
			echo $script_output;
			ob_flush();
		}
	}
	$GLOBALS['p_last_time'] = P_EXEC_TIME;
	pines_print_time('Script Timing Start');
}

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
	include_once("system/classes/$cur_class");
}
unset($temp_classes);
if (P_SCRIPT_TIMING) pines_print_time('Load System Classes');

require_once('system/load.php');
$config_array = require('system/configure.php');
fill_object($config_array, $config);
unset($config_array);
if (P_SCRIPT_TIMING) pines_print_time('Load System Config');

// Check the offline mode, and load the offline page if enabled.
if ($config->offline_mode) {
	include('system/offline.php');
	exit;
}

/**
 * An array of the enabled components.
 * @global array $config->components
 */
$config->components = array();
if ( file_exists("components/") ) {
	$config->components = pines_scandir("components/");
}

/**
 * An array of all components.
 * @global array $config->all_components
 */
$config->all_components = array();
if ( file_exists("components/") ) {
	$config->all_components = pines_scandir("components/", 0, null, false);
	foreach ($config->all_components as $cur_key => $cur_value) {
		if (substr($cur_value, 0, 1) == '.')
			$config->all_components[$cur_key] = substr($cur_value, 1);
	}
}
if (P_SCRIPT_TIMING) pines_print_time('Find Component Classes');

// Load component classes.
/**
 * List of class files for autoloading classes.
 * @var $config->class_files
 */
$config->class_files = array();
foreach ($config->components as $cur_component) {
	if ( is_dir("components/$cur_component/classes/") ) {
		$temp_classes = pines_scandir("components/$cur_component/classes/");
		foreach ($temp_classes as $cur_class) {
			$config->class_files[preg_replace('/\.php$/', '', $cur_class)] = "components/$cur_component/classes/$cur_class";
		}
		unset($temp_classes);
	}
}
/**
 * Load a class file.
 *
 * @param string $class_name The class name.
 */
function __autoload($class_name) {
	global $config;
	if (key_exists($class_name, $config->class_files)) {
		include_once($config->class_files[$class_name]);
		if (P_SCRIPT_TIMING) pines_print_time("Load [$class_name]");
	}
}

// Now that all classes are loaded, we can start the session manager. This
// allows variables to keep their classes over sessions.
session_start();

// Make a random secret that only this instance knows, so we can pass secret
// vars in hook objects.
$_SESSION['secret'] = rand();

// Load the config for our components.
foreach ($config->components as $cur_component) {
	if ( file_exists("components/$cur_component/configure.php") ) {
		$config_array = include("components/$cur_component/configure.php");
		if (is_array($config_array)) {
			$config->$cur_component = new p_base;
			fill_object($config_array, $config->$cur_component);
		}
		unset($config_array);
	}
}
if (P_SCRIPT_TIMING) pines_print_time('Load Component Config');

// Load the hooks for $config.
$config->hook->hook_object($config, '$config->');
if (P_SCRIPT_TIMING) pines_print_time('Hook $config');

// Load the configuration for our components. This shouldn't require any sort of
// functionality, like entity or user management.
foreach ($config->components as $cur_component) {
	if ( file_exists("components/$cur_component/load.php") )
		include_once("components/$cur_component/load.php");
}
if (P_SCRIPT_TIMING) pines_print_time('Load Component Loaders');

// Load the display controller.
require_once('system/display.php');
if (P_SCRIPT_TIMING) pines_print_time('Load Display Controller');

// Load the hooks for $page.
$config->hook->hook_object($page, '$page->');
if (P_SCRIPT_TIMING) pines_print_time('Hook $page');

// Load the common files. This should set up the models for each component,
// which the actions should then use to manipulate actual data.
foreach ($config->components as $cur_component) {
	if ( file_exists("components/$cur_component/common.php") )
		include_once("components/$cur_component/common.php");
}
if (P_SCRIPT_TIMING) pines_print_time('Load Component Common');

// Load some common functions.
include_once('system/common.php');
if (P_SCRIPT_TIMING) pines_print_time('Load System Common');

// Load any post or get vars for our component/action.
$config->request_component = clean_filename($_REQUEST['option']);
$config->request_action = clean_filename($_REQUEST['action']);

// URL Rewriting Engine (Simple, eh?)
// The values from URL rewriting override any post or get vars, so don't submit
// forms to a url you shouldn't.
// /index.php/user/edituser/id-35/ -> /index.php?option=com_user&action=edituser&id=35
if ( $config->url_rewriting ) {
// Get an array of the pseudo directories from the URI.
	$args_array = explode('/',
		// Get rid of index.php/ at the beginning, and / at the end.
		preg_replace('/(^index\.php\/)|(\/$)/', '', substr(
		substr($_SERVER['REQUEST_URI'], 0,
		// Use the whole string, or if there's a query part, subtract that.
		strlen($_SERVER['REQUEST_URI']) - (strlen($_SERVER['QUERY_STRING']) ? strlen($_SERVER['QUERY_STRING']) + 1 : 0)
		),
		// This takes off the path to Pines.
		strlen($config->rela_location)
		))
	);
	if ( !empty($args_array[0]) ) $config->request_component = ($args_array[0] == 'system' ? $args_array[0] : 'com_'.$args_array[0]);
	if ( !empty($args_array[1]) ) $config->request_action = $args_array[1];
	$arg_count = count($args_array);
	for ($i = 2; $i < $arg_count; $i++) {
		$_REQUEST[preg_replace('/-.*$/', '', $args_array[$i])] = preg_replace('/^[^-]*-/', '', $args_array[$i]);
	}
	unset($i);
	unset($arg_count);
	unset($args_array);
}

// Fill in any empty request vars.
if ( empty($config->request_component) ) $config->request_component = $config->default_component;
if ( empty($config->request_action) ) $config->request_action = 'default';
if (P_SCRIPT_TIMING) pines_print_time('Get Requested Action');

// Call the action specified.
if ( action($config->request_component, $config->request_action) === 'error_404' ) {
	header('HTTP/1.0 404 Not Found', true, 404);
	$error_page = new module('system', 'error_404', 'content');
}
if (P_SCRIPT_TIMING) pines_print_time('Run Requested Action');

// Load the final display stuff. This includes menu entries.
foreach ($config->components as $cur_component) {
	if ( file_exists("components/$cur_component/display.php") )
		include_once("components/$cur_component/display.php");
}
if (P_SCRIPT_TIMING) pines_print_time('Load Component Displays');

// Render the page.
$page->render();
if (P_SCRIPT_TIMING) pines_print_time('Render Page');

// If there's still a database connection, close it.
if ( isset($config->db_manager) )
	$config->db_manager->disconnect();
if (P_SCRIPT_TIMING) pines_print_time('Close Any Database', true);

/**
 * Fill an object with the data from a configuration array.
 *
 * The configuration array must be formatted correctly. It must contain one
 * array per variable, each with the following items:
 *
 * 'name' : The name of the variable.
 *
 * 'cname' : A common name for the variable. (A title)
 *
 * 'description' : A description of the variable.
 *
 * 'value' : The variable's actual value.
 *
 * @param array $config_array The configuration array to process.
 * @param mixed &$object The object to which the variables should be added.
 * @return bool True on success, false on failure.
 */
function fill_object($config_array, &$object) {
	if (!is_array($config_array)) return false;
	foreach ($config_array as $cur_var) {
		$name = $cur_var['name'];
		$value = $cur_var['value'];
		$object->$name = $value;
	}
	return true;
}

/**
 * Scan a directory and filter the results.
 *
 * Scan a directory and filter any dot files/dirs and "index.html" out of the
 * result.
 *
 * @param string $directory The directory that will be scanned.
 * @param int $sorting_order By default, the sorted order is alphabetical in ascending order. If the optional sorting_order is set to non-zero, then the sort order is alphabetical in descending order.
 * @param resource $context An optional context.
 * @param bool $hide_dot_files Whether to hide filenames beginning with a dot.
 * @return array|false The array of filenames on success, false on failure.
 */
function pines_scandir($directory, $sorting_order = 0, $context = null, $hide_dot_files = true) {
	if (is_null($context)) {
		if (!($return = scandir($directory, $sorting_order))) return false;
	} else {
		if (!($return = scandir($directory, $sorting_order, $context))) return false;
	}
	foreach ($return as $cur_key => $cur_name) {
		if ( (stripos($cur_name, '.') === 0 && $hide_dot_files) || (in_array($cur_name, array('index.html', '.', '..', '.svn'))) )
			unset($return[$cur_key]);
	}
	return array_values($return);
}

/**
 * Strip slashes from an array recursively.
 *
 * Only processes strings.
 *
 * @param array &$array The array to process.
 * @return bool True on success, false on failure.
 */
function pines_stripslashes_array_recursive(&$array) {
	if (!is_array($array)) return false;
	foreach ($array as &$cur_item) {
		if (is_array($cur_item)) {
			pines_stripslashes_array_recursive($cur_item);
		} elseif (is_string($cur_item)) {
			$cur_item = stripslashes($cur_item);
		}
	}
	return true;
}
?>
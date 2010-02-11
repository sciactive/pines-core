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
 * When this is set to true, the times between script stages will be displayed.
 */
define('P_SCRIPT_TIMING', false);

if (P_SCRIPT_TIMING) {
	/**
	 * Display a message for Pines Script Timing.
	 *
	 * Messages will be displayed in the FireBug console, if available, or an
	 * alert() if not.
	 *
	 * @param string $message The message.
	 * @param bool $print_now Whether to print the page now.
	 */
	function pines_print_time($message, $print_now = false) {
		static $time_output;
		$microtime = microtime(true);
		$time_output .= sprintf(str_pad($message, 40).'%F\n', ($microtime - $GLOBALS['p_last_time']));
		$GLOBALS['p_last_time'] = $microtime;
		if ($print_now) {
			echo '<script type="text/javascript">
(function(message){
	if (console.log) {
		console.log(message);
	} else {
		alert(message);
	}
})("';
			echo 'Pines Script Timing\n\nTimes are measured in seconds.\n';
			echo $time_output;
			printf('--------------------\n'.str_pad('Script Run', 40).'%F', ($microtime - P_EXEC_TIME));
			echo '");</script>';
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
/**
 * The current template.
 *
 * @global string $pines->current_template
 */
$pines->current_template = ( !empty($_REQUEST['template']) && $pines->allow_template_override ) ?
	$_REQUEST['template'] : $pines->default_template;
$pines->template = $pines->current_template;
date_default_timezone_set($pines->timezone);
if (P_SCRIPT_TIMING) pines_print_time('Load System Config');

// Check the offline mode, and load the offline page if enabled.
if ($pines->offline_mode)
	require('system/offline.php');

/**
 * An array of the enabled components.
 * @global array $pines->components
 */
$pines->components = array();
/**
 * An array of all components.
 * @global array $pines->all_components
 */
$pines->all_components = array();
if ( file_exists('components/') && file_exists('templates/') ) {
	$pines->components = array_merge(pines_scandir('components/'), pines_scandir('templates/'));
	$pines->all_components = array_merge(pines_scandir('components/', 0, null, false), pines_scandir('templates/', 0, null, false));
	foreach ($pines->all_components as $cur_key => $cur_value) {
		if (substr($cur_value, 0, 1) == '.')
			$pines->all_components[$cur_key] = substr($cur_value, 1);
	}
}
if (P_SCRIPT_TIMING) pines_print_time('Find Component Classes');

// Load component classes.
/**
 * List of class files for autoloading classes.
 *
 * Note that templates have a classes dir, but the only file loaded from it is
 * the file of the same name as the template. Also, only the current template's
 * class is loaded.
 *
 * @var array $pines->class_files
 */
$pines->class_files = array();
foreach ($pines->components as $cur_component) {
	if (substr($cur_component, 0, 4) == 'tpl_')
		continue;
	if ( is_dir("components/$cur_component/classes/") ) {
		$temp_classes = pines_scandir("components/$cur_component/classes/");
		foreach ($temp_classes as $cur_class) {
			$pines->class_files[preg_replace('/\.php$/', '', $cur_class)] = "components/$cur_component/classes/$cur_class";
		}
	}
}
unset($temp_classes);
// This variable name is misleading.
$cur_component = "templates/{$pines->current_template}/classes/{$pines->current_template}.php";
// If the current template is missing its class, display the template error page.
if ( !file_exists($cur_component) )
	require('system/template_error.php');
$pines->class_files[$pines->current_template] = $cur_component;
unset($cur_component);
/**
 * Load a class file.
 *
 * @param string $class_name The class name.
 */
function __autoload($class_name) {
	global $pines;
	// When session_start() tries to recover hooked objects, we need to make
	// sure their equivalent hooked classes exist.
	if (strpos($class_name, 'hook_override_') === 0) {
		$trace = debug_backtrace();
		// But the hook object will check if a hooked class exists before
		// hooking it, so we don't want to create an extra object each time.
		if ($trace[1]['function'] == 'class_exists')
			return;
		$new_class = preg_replace('/^hook_override_/', '', $class_name);
		$new_object = new $new_class;
		$pines->hook->hook_object($new_object, get_class($new_object).'->', false);
		unset($new_object);
		return;
	}
	if (key_exists($class_name, $pines->class_files)) {
		include_once($pines->class_files[$class_name]);
		if (P_SCRIPT_TIMING) pines_print_time("Load [$class_name]");
	}
}

// Now that all classes can be loaded, we can start the session manager. This
// allows variables to keep their classes over sessions.
session_start();

// Make a random secret that only this instance knows, so we can pass secret
// vars in hook objects.
$_SESSION['secret'] = rand();

// Load the hooks for $pines.
$pines->hook->hook_object($pines, '$pines->');
if (P_SCRIPT_TIMING) pines_print_time('Hook $pines');

// Run the loaders for our components. This shouldn't require any sort of
// functionality, like entity or user management.
foreach ($pines->components as $cur_component) {
	if (substr($cur_component, 0, 4) == 'tpl_')
		continue;
	if ( file_exists("components/$cur_component/load.php") )
		include_once("components/$cur_component/load.php");
}
if (P_SCRIPT_TIMING) pines_print_time('Run Component Loaders');

// Load the common files. This should set up component-dependent things, such as
// abilities.
foreach ($pines->components as $cur_component) {
	if (substr($cur_component, 0, 4) == 'tpl_')
		continue;
	if ( file_exists("components/$cur_component/common.php") )
		include_once("components/$cur_component/common.php");
}
if (P_SCRIPT_TIMING) pines_print_time('Load Component Common');

// Load some common functions.
include_once('system/common.php');
if (P_SCRIPT_TIMING) pines_print_time('Load System Common');

// Load any post or get vars for our component/action.
$pines->request_component = clean_filename($_REQUEST['option']);
$pines->request_action = clean_filename($_REQUEST['action']);

// URL Rewriting Engine (Simple, eh?)
// The values from URL rewriting override any post or get vars, so don't submit
// forms to a url you shouldn't.
// /index.php/user/edituser/id-35/ -> /index.php?option=com_user&action=edituser&id=35
if ( $pines->url_rewriting ) {
	// Get an array of the pseudo directories from the URI.
	$args_array = explode('/',
		// Get rid of index.php/ at the beginning, and / at the end.
		preg_replace('/(^index\.php\/?)|(\/$)/', '', substr(
		substr($_SERVER['REQUEST_URI'], 0,
		// Use the whole string, or if there's a query part, subtract that.
		strlen($_SERVER['REQUEST_URI']) - (strlen($_SERVER['QUERY_STRING']) ? strlen($_SERVER['QUERY_STRING']) + 1 : 0)
		),
		// This takes off the path to Pines.
		strlen($pines->rela_location)
		))
	);
	if ( !empty($args_array[0]) ) $pines->request_component = ($args_array[0] == 'system' ? $args_array[0] : 'com_'.$args_array[0]);
	if ( !empty($args_array[1]) ) $pines->request_action = $args_array[1];
	$arg_count = count($args_array);
	for ($i = 2; $i < $arg_count; $i++) {
		$_REQUEST[preg_replace('/-.*$/', '', $args_array[$i])] = preg_replace('/^[^-]*-/', '', $args_array[$i]);
	}
	unset($i);
	unset($arg_count);
	unset($args_array);
}

// Fill in any empty request vars.
if ( empty($pines->request_component) ) $pines->request_component = $pines->default_component;
if ( empty($pines->request_action) ) $pines->request_action = 'default';
if (P_SCRIPT_TIMING) pines_print_time('Get Requested Action');

// Call the action specified.
if ( action($pines->request_component, $pines->request_action) === 'error_404' ) {
	header('HTTP/1.0 404 Not Found', true, 404);
	$error_page = new module('system', 'error_404', 'content');
}
if (P_SCRIPT_TIMING) pines_print_time('Run Requested Action');

// Load the final display stuff. This should set up things like menu entries,
// extra modules, etc.
foreach ($pines->components as $cur_component) {
	if (substr($cur_component, 0, 4) == 'tpl_')
		continue;
	if ( file_exists("components/$cur_component/display.php") )
		include_once("components/$cur_component/display.php");
}
if (P_SCRIPT_TIMING) pines_print_time('Load Component Displays');

// Render the page.
echo $pines->page->render();
if (P_SCRIPT_TIMING) pines_print_time('Render Page', true);

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
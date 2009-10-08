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

// Make a random secret that only this instance knows, so we can pass secret
// vars in hook objects.
$_SESSION['secret'] = rand();

// Load system classes.
$temp_classes = scandir_pines("system/classes/");
foreach ($temp_classes as $cur_class) {
    include_once("system/classes/$cur_class");
}
unset($temp_classes);

session_start();

require_once('load.php');
$config_array = require('configure.php');
fill_object($config_array, $config);
unset($config_array);

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
	$config->components = scandir_pines("components/");
}

/**
 * An array of all components.
 * @global array $config->all_components
 */
$config->all_components = array();
if ( file_exists("components/") ) {
	$config->all_components = scandir_pines("components/", 0, null, false);
    foreach ($config->all_components as $cur_key => $cur_value) {
        if (substr($cur_value, 0, 1) == '.')
            $config->all_components[$cur_key] = substr($cur_value, 1);
    }
}

// Load component classes.
foreach ($config->components as $cur_component) {
    if ( is_dir("components/$cur_component/classes/") ) {
        $temp_classes = scandir_pines("components/$cur_component/classes/");
        foreach ($temp_classes as $cur_class) {
            include_once("components/$cur_component/classes/$cur_class");
        }
        unset($temp_classes);
    }
}

// Load the config for our components.
foreach ($config->components as $cur_component) {
	if ( file_exists("components/$cur_component/configure.php") ) {
		$config_array = include("components/$cur_component/configure.php");
        if (is_array($config_array)) {
            $config->$cur_component = new DynamicConfig;
            fill_object($config_array, $config->$cur_component);
        }
        unset($config_array);
    }
}

// Load the configuration for our components. This shouldn't require any sort of
// functionality, like entity or user management.
foreach ($config->components as $cur_component) {
	if ( file_exists("components/$cur_component/load.php") )
		include_once("components/$cur_component/load.php");
}

// Load the hooks for $config.
$config->hook->hook_object($config, '$config->');

// Load the display controller.
require_once('display.php');

// Load the hooks for $page.
$config->hook->hook_object($page, '$page->');

// Load the common files. This should set up the models for each component,
// which the actions should then use to manipulate actual data.
foreach ($config->components as $cur_component) {
	if ( file_exists("components/$cur_component/common.php") )
		include_once("components/$cur_component/common.php");
}

// Load some common functions.
include_once('common.php');

// Load any post or get vars for our component/action.
$config->component = clean_filename($_REQUEST['option']);
$config->action = clean_filename($_REQUEST['action']);

// URL Rewriting Engine (Simple, eh?)
// The values from URL rewriting override any post or get vars, so don't submit
// forms to a url you shouldn't.
if ( $config->url_rewriting ) {
	$args_array = explode('/', substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME'])));
	if ( isset($args_array[1]) ) $config->component = ($args_array[1] == 'system' ? $args_array[1] : 'com_'.$args_array[1]);
	if ( isset($args_array[2]) ) $config->action = $args_array[2];
	unset($args_array);
}

// Fill in any empty request vars.
if ( empty($config->component) ) $config->component = $config->default_component;
if ( empty($config->action) ) $config->action = 'default';

// Call the action specified.
$action_file = ($config->component == 'system' ? $config->component : "components/$config->component")."/actions/$config->action.php";
if ( file_exists($action_file) ) {
    require($action_file);
} else {
    header('HTTP/1.0 404 Not Found', true, 404);
    $error_page = new module('system', 'error_404', 'content');
    $error_page->title = 'Error 404: Page not Found.';
}

// Load the final display stuff. This includes menu entries.
foreach ($config->components as $cur_component) {
	if ( file_exists("components/$cur_component/display.php") )
		include_once("components/$cur_component/display.php");
}

// Render the page.
$page->render();

// If there's still a database connection, close it.
if ( isset($config->db_manager) )
	$config->db_manager->disconnect();

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
function scandir_pines($directory, $sorting_order = 0, $context = null, $hide_dot_files = true) {
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
?>
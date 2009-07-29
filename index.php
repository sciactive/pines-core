<?php
/**
 * The controller for Pines' architecture.
 * 
 * @package Pines
 * @subpackage Core
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

session_start();

require_once('configure.php');
/**
 * An array of the installed components.
 * @global array $config->components
 */
$config->components = array();
if ( file_exists("components/") ) {
	$config->components = scandir("components/");
	foreach ($config->components as $cur_key => $cur_name) {
		if ( (stripos($cur_name, '.') === 0) || ($cur_name == 'index.html') )
			unset($config->components[$cur_key]);
	}
	$config->components = array_values($config->components);
}

// Load the configuration for our components. This shouldn't require any sort of
// functionality, like entity or user management.
foreach ($config->components as $cur_component) {
	if ( file_exists("components/$cur_component/configure.php") )
		include_once("components/$cur_component/configure.php");
}
// Load the display controller.
require_once('display.php');
// Load the common files. This should set up the models for each component,
// which the actions should then use to manipulate actual data.
foreach ($config->components as $cur_component) {
	if ( file_exists("components/$cur_component/common.php") )
		include_once("components/$cur_component/common.php");
}
// Load some common functions.
require_once('common.php');

// Load any post or get vars for our component/action.
$component = clean_filename($_REQUEST['option']);
$action = clean_filename($_REQUEST['action']);

// URL Rewriting Engine (Simple, eh?)
// The values from URL rewriting override any post or get vars, so don't submit
// forms to a url you shouldn't.
if ( $config->url_rewriting ) {
	$args_array = explode('/', substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME'])));
	if ( isset($args_array[1]) ) $component = 'com_'.$args_array[1];
	if ( isset($args_array[2]) ) $action = $args_array[2];
	unset($args_array);
}

// Fill in any empty request vars.
if ( empty($component) ) $component = $config->default_component;
if ( empty($action) ) $action = 'default';

// Call the action specified.
if ( file_exists("components/$component/actions/$action.php") ) {
    require("components/$component/actions/$action.php");
} else {
    display_error("Action not defined! D:");
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
 * Fill an object with the data from a WDDX string.
 *
 * The WDDX string must be formatted correctly. It must contain one array which
 * contains an array per variable, each with the following items:
 *
 * 'name' : The name of the variable.
 * 'cname' : A common name for the variable. (A title)
 * 'description' : A description of the variable.
 * 'value' : The variable's actual value.
 *
 * @param string $wddx_data The WDDX string to process.
 * @param mixed &$object The object to which the variables should be added.
 * @return bool True on success, false on failure.
 */
function fill_object($wddx_data, &$object) {
    $wddx = wddx_deserialize($wddx_data);
    if (!is_array($wddx)) return false;
    foreach ($wddx as $cur_var) {
        $name = $cur_var['name'];
        $value = $cur_var['value'];
        $object->$name = $value;
    }
    return true;
}
?>
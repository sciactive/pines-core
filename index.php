<?php
/**
 * index.php
 *
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package Dandelion
 */

/*
 * Dandelion - a Lightweight PHP Application Framework
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

define('D_RUN', true);
define('D_BASE_PATH', dirname(__FILE__));
define('D_INDEX', basename($_SERVER['SCRIPT_FILENAME']));

session_start();

require_once('configure.php');
$config->components = array();
if ( file_exists("components/") ) {
	$config->components = scandir("components/");
	foreach ($config->components as $cur_key => $cur_name) {
		if ( (stripos($cur_name, '.') === 0) || ($cur_name == 'index.html') )
			unset($config->components[$cur_key]);
	}
	$config->components = array_values($config->components);
}

foreach ($config->components as $cur_component) {
	if ( file_exists("components/$cur_component/configure.php") )
		include_once("components/$cur_component/configure.php");
}
require_once('display.php');
foreach ($config->components as $cur_component) {
	if ( file_exists("components/$cur_component/common.php") )
		include_once("components/$cur_component/common.php");
}
require_once('common.php');

$action = clean_filename($_REQUEST['action']);
$component = clean_filename($_REQUEST['option']);
// URL Rewriting Engine (Simple, eh?)
if ( $config->url_rewriting ) {
	$args_array = explode('/', substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME'])));
	if ( isset($args_array[1]) ) $component = 'com_'.$args_array[1];
	if ( isset($args_array[2]) ) $action = $args_array[2];
	unset($args_array);
}

if ( !empty($component) ) {
	if ( empty($action) ) $action = 'default';
	
	if ( file_exists("components/$component/actions/$action.php") ) {
		require("components/$component/actions/$action.php");
	} else {
		display_error("Action not defined! D:");
	}
} else {
	print_default();
}

foreach ($config->components as $cur_component) {
	if ( file_exists("components/$cur_component/display.php") )
		include_once("components/$cur_component/display.php");
}

$page->render();

if ( isset($config->db_manager) )
	$config->db_manager->disconnect();
?>
<?php
/**
 * Run the action.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

// Load any post or get vars for our component/action.
$pines->request_component = clean_filename($_REQUEST['option']);
$pines->request_action = clean_filename($_REQUEST['action']);

// URL Rewriting Engine (Simple, eh?)
// The values from URL rewriting override any post or get vars, so don't submit
// forms to a url you shouldn't.
// /index.php/user/edituser/id-35/ -> /index.php?option=com_user&action=edituser&id=35
if ( $pines->config->url_rewriting ) {
	// Get an array of the pseudo directories from the URI.
	$args_array = explode('/',
		// Get rid of index.php/ at the beginning, and / at the end.
		preg_replace('/(^index\.php\/?)|(\/$)/', '', substr(
		substr($_SERVER['REQUEST_URI'], 0,
		// Use the whole string, or if there's a query part, subtract that.
		strlen($_SERVER['REQUEST_URI']) - (strlen($_SERVER['QUERY_STRING']) ? strlen($_SERVER['QUERY_STRING']) + 1 : 0)
		),
		// This takes off the path to Pines.
		strlen($pines->config->rela_location)
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
if ( empty($pines->request_component) ) $pines->request_component = $pines->config->default_component;
if ( empty($pines->request_action) ) $pines->request_action = 'default';
if (P_SCRIPT_TIMING) pines_print_time('Get Requested Action');

// Call the action specified.
if ( action($pines->request_component, $pines->request_action) === 'error_404' ) {
	header('HTTP/1.0 404 Not Found', true, 404);
	$error_page = new module('system', 'error_404', 'content');
}
if (P_SCRIPT_TIMING) pines_print_time('Run Requested Action');

?>
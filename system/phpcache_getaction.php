<?php

/*
 * Pines PHP Caching - Get Requested Action
 * 
 * This file does the dirty work of getting the requested action
 * So that we can build or retrieve the appropriate filename.
 * 
 * @package Core
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author1 Angela Murrell <amasiell.g@gmail.com>
 * @author2 Grey Vugrin <greyvugrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 * 
 */

// Load any post or get vars for our component/action.
$request_component = str_replace('..', 'fail-danger-dont-use-hack-attempt', $_REQUEST['option']);
$request_action = str_replace('..', 'fail-danger-dont-use-hack-attempt', $_REQUEST['action']);
$query_string = 'q.'.$request_component.'.'.md5(serialize(str_replace('..', 'fail-danger-dont-use-hack-attempt', $_SERVER['QUERY_STRING'])));

// Do this to get rid of query string - if there is nothing besides possibly
// option and action. If there really IS something, it will preserve.
// If there IS but only interpretted through url rewriting, it'll get added below.
$requested_vars = $_REQUEST;
unset($requested_vars['option']);
unset($requested_vars['action']);
if (count($requested_vars) == 0)
	unset($query_string);

// There was no request component
if (empty($request_component)) {
	// We are either on the "home" or we have URL rewriting on.
	$script_name = $_SERVER['SCRIPT_NAME'];
	// get rid of index and get the install location. ie /pines/ or /
	$root = preg_replace('#(.*)index\.php#', '$1', $script_name);
	$root = preg_replace('#/$#', '', $root); // '/pines' or ''
	$request_uri = preg_replace('#/$#', '', $_SERVER['REQUEST_URI']);
	// No install location or index.php or definite query
	$cleaned_uri = preg_replace('#'.$root.'(index\.php)?(.*)'.$_SERVER['QUERY_STRING'].'#', '$2', $request_uri);
	$cleaned_uri = preg_replace('#\?$#', '', $cleaned_uri);
	$cleaned_uri = preg_replace('#^/#', '', $cleaned_uri);
	
	if ($cleaned_uri == '') {
		// Home page (url rewriting or not)
		$request_component = '';
		$request_action = '';
	} else {
		// Url Rewrite - We need an option, action
		// Get DEFINITE query string
		if (strlen($_SERVER['QUERY_STRING']))
			$definite_query = preg_replace("#.*$request_uri#", '', $_SERVER['QUERY_STRING']);
		// Get option
		// Check for no action
		$component = preg_replace('#([^/]*).*#', '$1', $cleaned_uri);
		$request_component = 'com_'.$component;
		$possible_action = preg_replace('#^'.$component.'#', '', $cleaned_uri).'/';
		$matched_action = preg_match('#(\/[^-]*\/)#', $possible_action, $matches);
		if (empty($matches[0])) {
			// This grabs the component from uris with no action,
			// possibly definite query data, or possibly url rewritten query data.
			$action = $request_action = '';
			// Get remaining query
			$possible_query = preg_replace('#(\/[^-]*\/)#', '', $possible_action);
		} else {
			// Get action
			//$action = preg_replace('#^/(.*)/.*?-.*#', '$1', $possible_action);
			$request_action = preg_replace('#(\/[^-]*\/).*#', '$1', $possible_action);
			// Get remaining query
			$possible_query = preg_replace('#(\/[^-]*\/)#', '', $possible_action);
		}
		$possible_query = preg_replace('#\/$#', '', $possible_query);
		$request_action = preg_replace('#\/$#', '', $request_action);
		$request_action = preg_replace('#^\/#', '', $request_action);
		
		// Combine + hash query?
		if (!empty($possible_query) || !empty($definite_query)) {
			$query_string = 'q.'.$component.'.';
			if (empty($possible_query))
				$query_string .= md5(serialize($definite_query));
			else if (empty($possible_query))
				$query_string .= md5(serialize($possible_query));
			else
				$query_string .= md5(serialize($definite_query.$possible_query));
		} else
			unset($query_string);
		
		$request_action = preg_replace('#\/$#', '', $request_action);
	}
}
?>

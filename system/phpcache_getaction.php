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
	$root = preg_replace('/index\.php/', '', $script_name);
	$request_uri = $_SERVER['REQUEST_URI'];
	$request_uri = substr($request_uri, 0, (strlen($_SERVER['QUERY_STRING']) * -1) - 1);
	
	if ($request_uri.'/' == $root) {
		// Home page (url rewriting or not)
		$request_component = '';
		$request_action = '';
	} else {
		// Url Rewrite - We need an option, action
		
		// Get DEFINITE query string
		if (strlen($_SERVER['QUERY_STRING']))
			$definite_query = preg_replace("#$request_uri#", '', $_SERVER['QUERY_STRING']);
		
		// No index.php
		$cleaned_uri = preg_replace('/index\.php/', '', $request_uri);
		
		// Get option
		// Check for no action 
		if (preg_match('#'.$root.'([^/]*)(/$|$|/\?.*|/[^/]*-.*)#', $cleaned_uri)) {
			// This grabs the component from uris with no action,
			// possibly definite query data, or possibly url rewritten query data.
			$component = preg_replace('#'.$root.'([^/]*)(/$|$|/\?.*|/[^/]*-.*)#', '$1', $cleaned_uri);
			$action = '';
			$request_action = '';
			// Get remaining query
			$possible_query = preg_replace('#'.$root.$component.'/?#', '', $cleaned_uri);
		} else {
			$component = preg_replace('#^'.$root.'(.*?)/.*#', '$1', $cleaned_uri);
			
			// Get action
			$removed_option = preg_replace('#'.$root.$component.'#', '', $cleaned_uri);
			$action = preg_replace('#^/(.*)/.*?-.*#', '$1', $removed_option);
			$request_action = preg_replace('#^/(.*)/?$#', '$1', $action);
			
			// Get remaining query
			$possible_query = preg_replace('#'.$root.$component.'/'.$request_action.'/?'.'#', '', $request_uri);
		}
		$request_component = 'com_'.$component;
		
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

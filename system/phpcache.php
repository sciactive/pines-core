<?php
/*
 * Pines PHP Caching
 * 
 * This file will read the cachelist.php file in this directory
 * that you copy from the sample_cachelist.php and edit to your liking.
 * 
 * Pines Caching will - for now - only work with non-logged in users,
 * ensuring that "static pages" get cached and dynamic ones do not.
 * 
 * Useful for landing pages/homepages etc.
 * 
 * 
 * 
 * @package Core
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author1 Angela Murrell <amasiell.g@gmail.com>
 * @author2 Grey Vugrin <greyvugrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 * 
 *
 */

function stripslashes_array_recursive(&$array) {
	if ((array) $array !== $array)
		return false;
	foreach ($array as &$cur_item) {
		if ((array) $cur_item === $cur_item)
			stripslashes_array_recursive($cur_item);
		elseif (is_string($cur_item))
			$cur_item = stripslashes($cur_item);
	}
	return true;
}
if (get_magic_quotes_gpc()) {
	stripslashes_array_recursive($_GET);
	stripslashes_array_recursive($_POST);
	stripslashes_array_recursive($_REQUEST);
	stripslashes_array_recursive($_COOKIE);
}

// Before session is started:
// We need to override ini session timeouts here, 
// because we no longer hit timeout calls every 2 minutes to prevent timing out.
// We could set this forever, but we'll max it out from PHP's side of things to the user's max time out.
// Without this, the default ini settings were timing out users instead of letting us manage it.
ini_set("session.gc_maxlifetime", 86400);

// Present the Opportunity for PHP Caching.
@session_start();
if (isset($_SESSION['user_id'])) {
	//var_dump($_SESSION);
	if (isset($_SESSION['inherited_abilities']))
		$abilities = $_SESSION['inherited_abilities'];
	else 
		$abilities = $_SESSION['abilities'];
	asort($abilities);
	$hash = 'a'.md5(serialize($abilities));
	$pnotice = $_SESSION['p_notices'];
	$perror = $_SESSION['p_errors'];
	
//	$cache_access = 'The timeout is: '.$_SESSION['com_timeoutnotice__timeout']."\n";
//	$cache_access .= 'The last access is: '.$_SESSION['com_timeoutnotice__last_access']."\n";
//	$cache_access .= 'The time minus access is: '.(time() - $_SESSION['com_timeoutnotice__last_access'])."\n";
	
	@session_write_close();
} else {
	@session_unset();
	@session_destroy();
}

if ($_REQUEST['cache'] == 'off')
	return;
if ($_REQUEST['cache'] == 'refresh')
	$refresh_cache = true;

// Include the cachelist
if (!file_exists('system/cacheoptions.php'))
	return;
$cacheoptions = include('system/cacheoptions.php');
// Read options, like if cache is on
if (!$cacheoptions['cache_on'])
	return;

// Get component, action, query string
include('system/phpcache_getaction.php');
$cachelist = $cacheoptions['cachelist'];

// Do not cache the component com_cache
if ($request_component == 'com_cache')
	return;

// Find the option/action combo in the cachelist - leave if no result.
if (isset($cachelist[$request_component][$request_action])) {
	$cur_domain = $_SERVER['SERVER_NAME'];
	$use_domain = (isset($cachelist[$request_component][$request_action]['all'])) ? 'all' : $cur_domain;
	// Check domain first, otherwise I cannot get options.
	if (!isset($cachelist[$request_component][$request_action][$use_domain]))
		return;
	// Check if the directive is disabled.
	if ($cachelist[$request_component][$request_action][$use_domain]['disabled'])
		return;
	// See if we should cache with the user logged in.
	if (isset($hash) && !$cachelist[$request_component][$request_action][$use_domain]['cacheloggedin'])
		return;
	// Check if we want query data or not.
	if (!empty($query_string) && !$cachelist[$request_component][$request_action][$use_domain]['cachequery'])
		return; // We have query string, but we should NOT cache it.
	// If cachequery is set to true, it will cache the file if we have query data
	// or even if we do not. They will be retrieved from different directories though.
	
	// Check if caching queries on, and exceptions is not empty - important not to use 
	// query string only as a test because we have to check post data too.
	if (!empty($cachelist[$request_component][$request_action][$use_domain]['exceptions']) && $cachelist[$request_component][$request_action][$use_domain]['cachequery']) {
		// This one checks if the variable name isset in the request array.
		if (!empty($cachelist[$request_component][$request_action][$use_domain]['exceptions']['isset'])) {
			foreach($cachelist[$request_component][$request_action][$use_domain]['exceptions']['isset'] as $cur_isset) {
				if (isset($_REQUEST[$cur_isset])) {
					return;
				}
			}
		}
		// This one checks if the variable == the value, in the request array.
		if (!empty($cachelist[$request_component][$request_action][$use_domain]['exceptions']['value'])) {
			foreach($cachelist[$request_component][$request_action][$use_domain]['exceptions']['value'] as $cur_name => $value_array) {
				foreach ($value_array as $cur_value) {
					if ($_REQUEST[$cur_name] == $cur_value) {
						return;
					}
				}
			}
		}
	}
} else {
	return;
}

if ($request_action == '')
	$file_name = $request_component;
else
	$file_name = $request_component.'.'.$request_action;

if ($file_name == '')
	$file_name = 'home';

$file_name = preg_replace('#/#', '.', $file_name);

// See if pinescache dir exists, if not make it.
$parent_directory = $cacheoptions['parent_directory'];
	if (!is_dir($parent_directory))
		mkdir($parent_directory, 0700, true);

// See if domain dir exists, if not make it.
$domain_directory = $parent_directory.$_SERVER['SERVER_NAME'].'/';
	if (!is_dir($domain_directory))
		mkdir($domain_directory, 0700, true);

$directory_set = $domain_directory;
	
// If abilities hash, Check directory
if (isset($hash)) {
	$abil_hash_directory = $directory_set.$hash.'/';
		if (!is_dir($abil_hash_directory))
			mkdir($abil_hash_directory, 0700, true);
	$directory_set = $abil_hash_directory;
}

// Figure out from $cachelist['cachelist'] if we need to search in a query folder
if (!empty($query_string) && $cachelist[$request_component][$request_action][$use_domain]['cachequery']) {
	$query_directory = $directory_set.$query_string.'/';
	if (!is_dir($query_directory))
		mkdir($query_directory, 0700, true);
	$directory_set = $query_directory;
}

$final_directory = $directory_set;
$path = $final_directory.$file_name.'.html';

@session_start();
// What if the time is already not set or outdated.
// Update the access time for time outs:
if ($hash) {
	// We need to check if they should be logged out.
	$calc_timeout = $_SESSION['com_timeoutnotice__timeout'];
	if ( isset($_SESSION['com_timeoutnotice__last_access']) && (time() - $_SESSION['com_timeoutnotice__last_access'] >= $calc_timeout) ) {
		@session_write_close();
		return; // Go through inits which will log the user out.
	} else {
		// Set the access time
		$_SESSION['com_timeoutnotice__last_access'] = time();
	}
}

// If the refresh cache is true, it will force it to regenerate.
if (!$refresh_cache && file_exists($path) && ((time() - $cachelist[$request_component][$request_action][$use_domain]['time']) < filemtime($path))) {
	$pnotices = '';
	$perrors = '';
	
//	file_put_contents("/tmp/pines.log", $cache_access, FILE_APPEND);
	// Get notices and errors
	if (!empty($pnotice)) {
		foreach ($pnotice as $cur_notice) {
			$pnotices .= "pines.notice('".$cur_notice."');\n";
		}
	}
	if (!empty($perror)) {
		foreach ($perror as $cur_error) {
			$perrors .= "pines.error('".$cur_error."');\n";
		}
	}
	// Clean content for notices.
	$content_notices = preg_replace("#pines.notice\(\"\s.*\);#", "$pnotices", file_get_contents($path));
	// Clean content for errors.
	$content = preg_replace("#pines.error\(\"\s.*\);#", "$perrors", $content_notices);
	
	// Clean content for https links
	$replace_http = 'http'.(($_SERVER['HTTPS'] == 'on') ? 's://' : '://');
	$content = preg_replace('#http://'.$_SERVER['SERVER_NAME'].'#', $replace_http.$_SERVER['SERVER_NAME'], $content);
	@session_write_close();
	echo $content;
	exit;
} else {
	define('WRITECACHEFILE', true);
	define('WRITECACHEPATH', $path);
}

@session_write_close();



?>
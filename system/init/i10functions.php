<?php
/**
 * Define some basic functions and shortcuts.
 *
 * @package Core
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (P_SCRIPT_TIMING) pines_print_time('Define Basic Functions');

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
	if (isset($context)) {
		if (!($return = scandir($directory, $sorting_order, $context)))
			return false;
	} else {
		if (!($return = scandir($directory, $sorting_order)))
			return false;
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
	if ((array) $array !== $array)
		return false;
	foreach ($array as &$cur_item) {
		if ((array) $cur_item === $cur_item)
			pines_stripslashes_array_recursive($cur_item);
		elseif (is_string($cur_item))
			$cur_item = stripslashes($cur_item);
	}
	return true;
}

/**
 * Sort by only the file's name.
 *
 * If the file's names are equal, then the entire string is compared using
 * strcmp(), otherwise, only the filename is compared.
 *
 * @param string $a The first file.
 * @param string $b The second file.
 * @return int Compare result.
 */
function pines_sort_by_filename($a, $b) {
	$str1 = strrchr($a, '/');
	$str2 = strrchr($b, '/');
	if ($str1 == $str2) {
		if ($a < $b)
			return -1;
		if ($a > $b)
			return 1;
		return 0;
	} else {
		if ($str1 < $str2)
			return -1;
		if ($str1 > $str2)
			return 1;
		return 0;
	}
}

/*
 * Some shortcuts, to make life easier.
 */

/**
 * Shortcut to $pines->action().
 *
 * @uses pines::action() Forwards parameters and returns the result.
 * @param string $component The component in which the action resides.
 * @param string $action The action to run.
 * @return mixed The value returned by the action.
 */
function pines_action($component = null, $action = null) {
	global $pines;
	return $pines->action($component, $action);
}

/**
 * Shortcut to $pines->redirect().
 *
 * @uses pines::redirect() Forwards parameters and returns the result.
 * @param string $url The URL to send the user to.
 * @param int $code The HTTP code to send to the browser.
 */
function pines_redirect($url, $code = 303) {
	global $pines;
	$pines->redirect($url, $code);
}

/**
 * Shortcut to $pines->format_content().
 *
 * @uses pines::format_content() Forwards parameters and returns the result.
 * @param string $content The content to format.
 * @return string The formatted content.
 */
function format_content($content) {
	global $pines;
	return $pines->format_content($content);
}

/**
 * Shortcut to $pines->format_date().
 *
 * @uses pines::format_date() Forwards parameters and returns the result.
 * @param int $timestamp The timestamp to format.
 * @param string $type The type of formatting to use.
 * @param string $format The format to use if type is 'custom'.
 * @param DateTimeZone|string|null $timezone The timezone to use for formatting. Defaults to date_default_timezone_get().
 * @return string The formatted date.
 */
function format_date($timestamp, $type = 'full_sort', $format = '', $timezone = null) {
	global $pines;
	return $pines->format_date($timestamp, $type, $format, $timezone);
}

/**
 * Shortcut to $pines->format_date_range().
 *
 * @uses pines::format_date_range() Forwards parameters and returns the result.
 * @param int $start_timestamp The timestamp of the beginning of the date range.
 * @param int $end_timestamp The timestamp of the end of the date range.
 * @param string $format The format to use. See the function description for details on the format.
 * @param DateTimeZone|string|null $timezone The timezone to use for formatting. Defaults to date_default_timezone_get().
 * @return string The formatted date range.
 */
function format_date_range($start_timestamp, $end_timestamp, $format = null, $timezone = null) {
	global $pines;
	return $pines->format_date_range($start_timestamp, $end_timestamp, $format, $timezone);
}

/**
 * Shortcut to $pines->format_fuzzy_time().
 *
 * @uses pines::format_fuzzy_time() Forwards parameters and returns the result.
 * @param int $timestamp The timestamp to format.
 * @return string Fuzzy time string.
 */
function format_fuzzy_time($timestamp) {
	global $pines;
	return $pines->format_fuzzy_time($timestamp);
}

/**
 * Shortcut to $pines->format_phone().
 *
 * @uses pines::format_phone() Forwards parameters and returns the result.
 * @param string $number The phone number to format.
 * @return string The formatted phone number.
 */
function format_phone($number) {
	global $pines;
	return $pines->format_phone($number);
}

/**
 * Shortcut to $pines->page->error().
 *
 * @uses page::error() Forwards parameters and returns the result.
 * @param string $text Information to display to the user.
 */
function pines_error($text) {
	global $pines;
	$pines->page->error($text);
}

/**
 * Shortcut to $pines->page->notice().
 *
 * @uses page::notice() Forwards parameters and returns the result.
 * @param string $text Information to display to the user.
 */
function pines_notice($text) {
	global $pines;
	$pines->page->notice($text);
}

/**
 * Shortcut to $pines->user_manager->gatekeeper().
 *
 * The gatekeeper() function should be defined in whatever component is taking
 * over user management. gatekeeper() without arguments should return false if
 * the current user is not logged in, true if he is. If he is, gatekeeper()
 * should take an "ability" argument which returns true if the user has the
 * required permissions. gatekeeper() should also take a "user" argument to
 * check whether a different user has an ability. This helps user managers use a
 * "login" ability, which can be used to disable an account.
 *
 * @uses user_manager_interface::gatekeeper() Forwards parameters and returns the result.
 * @param string $ability The ability to provide.
 * @param user $user The user to provide.
 * @return bool The result is returned if there is a user management component, otherwise it returns true.
 */
function gatekeeper($ability = null, $user = null) {
	global $pines;
	static $user_manager;
	if (!isset($user_manager)) {
		if (!isset($pines->user_manager))
			return true;
		$user_manager =& $pines->user_manager;
	}
	return $user_manager->gatekeeper($ability, $user);
}

/**
 * Shortcut to $pines->user_manager->punt_user().
 *
 * The punt_user() function should be defined in whatever component is taking
 * over user management. punt_user() must always end the execution of the
 * script. If there is no user management component, the user is directed to the
 * home page and the script terminates.
 *
 * @uses user_manager_interface::punt_user() Forwards parameters and returns the result.
 * @param string $message An optional message to display to the user.
 * @param string $url An optional URL to be included in the query data of the redirection url.
 * @return bool The result is returned if there is a user management component, otherwise it returns true.
 */
function punt_user($message = null, $url = null) {
	global $pines;
	if (!isset($pines->user_manager)) {
		header('Location: '.pines_url());
		exit($message);
	}
	$pines->user_manager->punt_user($message, $url);
}

/**
 * Shortcut to $pines->depend->check().
 *
 * @uses depend::check() Forwards parameters and returns the result.
 * @param string $type The type of dependency to be checked.
 * @param mixed $value The value to be checked.
 * @return bool The result of the dependency check.
 */
function pines_depend($type, $value) {
	global $pines;
	if (!isset($pines->depend))
		return true;
	return $pines->depend->check($type, $value);
}

/**
 * Shortcut to $pines->log_manager->log().
 *
 * @uses log_manager_interface::log() Forwards parameters and returns the result.
 * @return bool The result is returned if there is a log management component, otherwise it returns true.
 */
function pines_log() {
	global $pines;
	static $log_manager;
	if (!isset($log_manager)) {
		if (!isset($pines->log_manager))
			return true;
		$log_manager =& $pines->log_manager;
	}
	$args = func_get_args();
	return call_user_func_array(array($log_manager, 'log'), $args);
}

/**
 * Shortcut to $pines->session().
 *
 * @uses pines::session() Forwards parameters and returns the result.
 * @param string $option The type of access or action requested.
 */
function pines_session($option = 'read') {
	global $pines;
	return $pines->session($option);
}

/**
 * Shortcut to $pines->template->url().
 *
 * @uses template_interface::url() Forwards parameters and returns the result.
 * @return bool The result is returned if there is a template, otherwise it returns null.
 */
function pines_url() {
	global $pines;
	static $template;
	if (!isset($template)) {
		if (!isset($pines->template))
			return null;
		$template =& $pines->template;
	}
	$args = func_get_args();
	return call_user_func_array(array($template, 'url'), $args);
}

if (P_SCRIPT_TIMING) pines_print_time('Define Basic Functions');

?>
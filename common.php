<?php
/**
 * Common functions used in Pines.
 * 
 * These are often overriden by components, which is why this file needs to be
 * parsed after the components' common files.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!function_exists('action')) {
    /**
     * Load and run an action.
     *
     * @param string $component The component in which the action resides.
     * @param string $action The action to run.
     * @return mixed The value returned by the action, or 'error_404' if it doesn't exist.
     */
    function action($component, $action) {
        global $config, $page;
        $action_file = ($component == 'system' ? $component : "components/$component")."/actions/$action.php";
        $config->component = $component;
        $config->action = $action;
        unset($component);
        unset($action);
        if ( file_exists($action_file) ) {
            /**
             * Run the action's file.
             */
            return require($action_file);
        } else {
            return 'error_404';
        }
    }
}

if ( isset($config->ability_manager) ) {
    /**
     * The system/all ability let's the user perform any action on the system,
     * regardless of their other abilities.
     */
	$config->ability_manager->add('system', 'all', 'All Abilities', 'Let user do anything, regardless of whether they have the ability.');
}

/**
 * These are very rudamentary security functions. If you are worried about
 * security, you should consider replacing them.
 */
if (!function_exists('clean_checkbox')) {
    /**
     * Cleans an HTML checkbox's name, so that it can be parsed correctly by
     * PHP.
     *
     * @param string $name Name to clean.
     * @return string The cleaned name.
     */
	function clean_checkbox($name) {
		return str_replace('.', 'dot', urlencode($name));
	}
}

if (!function_exists('clean_filename')) {
    /**
     * Cleans a filename, so it doesn't refer to any parent directories.
     *
     * @param string $filename Filename to clean.
     * @return string The cleaned filename.
     */
	function clean_filename($filename) {
		return str_replace('..', 'fail-danger-dont-use-hack-attempt', $filename);
	}
}

if (!function_exists('is_clean_filename')) {
    /**
     * Checks whether a filename refers to any parent directories.
     *
     * @param string $filename Filename to check.
     * @return bool
     */
	function is_clean_filename($filename) {
		if ( strpos($filename, '..') === false ) {
			return true;
		} else {
			return false;
		}
	}
}

if (!function_exists('display_error')) {
    /**
     * Causes the system to report an error to the user.
     * 
     * This function should be used instead of calling $page->error directly,
     * because some admins may wish to log Pines errors, instead of
     * displaying them.
     *
     * @param string $error_text Information to display to the user.
     */
	function display_error($error_text) {
		global $page;
		$page->error($error_text);
	}
}

if (!function_exists('display_notice')) {
    /**
     * Causes the system to report a notice to the user.
     * 
     * This function should be used instead of calling $page->notice directly,
     * because some admins may wish to log Pines notices, instead of
     * displaying them.
     *
     * @param string $notice_text Information to display to the user.
     */
	function display_notice($notice_text) {
		global $page;
		$page->notice($notice_text);
	}
}

/**
 * Shortcut to $config->user_manager->gatekeeper().
 *
 * The gatekeeper() function should be defined in whatever component is taking
 * over user management. gatekeeper() without arguments should return false if
 * the current user is not logged in, true if he is. If he is, gatekeeper()
 * should take an "ability" argument which returns true if the user has the
 * required permissions. gatekeeper() should also take a "user" argument to
 * check whether a different user has an ability. This helps user managers use a
 * "login" ability, which can be used to disable an account.
 *
 * @uses $config->user_manager->gatekeeper() Forwards parameters and returns the result.
 * @param string $ability The ability to provide.
 * @param user $user The user to provide.
 * @return bool The result is returned if there is a user management component, otherwise it returns true.
 */
function gatekeeper($ability = NULL, $user = NULL) {
	global $config;
    if (is_null($config->user_manager))
        return true;
	return $config->user_manager->gatekeeper($ability, $user);
}

/**
 * Shortcut to $config->depend->check().
 *
 * @uses $config->depend->check() Forwards parameters and returns the result.
 * @return bool The result is returned from the dependency checker.
 */
function pines_depend() {
	global $config;
    if (is_null($config->depend))
        return true;
    $args = func_get_args();
    return call_user_func_array(array($config->depend, 'check'), $args);
}

/**
 * Shortcut to $config->log_manager->log().
 *
 * @uses $config->log_manager->log() Forwards parameters and returns the result.
 * @return bool The result is returned if there is a log management component, otherwise it returns true.
 */
function pines_log() {
	global $config;
    if (is_null($config->log_manager))
        return true;
    $args = func_get_args();
    return call_user_func_array(array($config->log_manager, 'log'), $args);
}

/**
 * Shortcut to $config->template->url().
 *
 * @uses $config->template->url() Forwards parameters and returns the result.
 * @return bool The result is returned if there is a template, otherwise it returns null.
 */
function pines_url() {
	global $config;
    if (is_null($config->template))
        return null;
    $args = func_get_args();
    return call_user_func_array(array($config->template, 'url'), $args);
}

?>
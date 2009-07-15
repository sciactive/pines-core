<?php
/**
 * Common functions used in Dandelion. These are often overriden by components,
 * which is why this file needs to be parsed after the components' common files.
 *
 * @package Dandelion
 * @subpackage Core
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('D_RUN') or die('Direct access prohibited');

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

if (!function_exists('print_default')) {
    /**
     * Notifies the user if there is no default component. This behavior will
     * change and this function will no longer exist in future versions.
     *
     * @deprecated
     */
	function print_default() {
		display_error("There is no installed component that has taken over the default print function!");
	}
}

if (!function_exists('display_error')) {
    /**
     * Causes the system to report an error to the user. This function should be
     * used instead of calling $page->error directly, because some admins may
     * wish to log Dandelion errors, instead of displaying them.
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
     * Causes the system to report a notice to the user. This function should be
     * used instead of calling $page->notice directly, because some admins may
     * wish to log Dandelion notices, instead of displaying them.
     *
     * @param string $notice_text Information to display to the user.
     */
	function display_notice($notice_text) {
		global $page;
		$page->notice($notice_text);
	}
}

if (!function_exists('gatekeeper')) {
    /**
     * The gatekeeper function should be defined by whatever component is taking
     * over user authentication. Standard practice is to return false if the user
     * is not logged in. If he is, gatekeeper should at least take an "ability"
     * argument which returns true if the user has the required permissions.
     *
     * @return bool Always true.
     */
	function gatekeeper() {
		return true;
	}
}

if (!function_exists('get_confirmation')) {
    /**
     * Gets confirmation from a user.
     *
     * @param string $heading The heading to display.
     * @param string $new_option The option to pass the response to.
     * @param string $new_action The action to pass the response to.
     * @param string $file An optional filename to pass.
     * @deprecated
     */
	function get_confirmation($heading, $new_option, $new_action, $file = "") {
		$module = new module('system', 'false', 'content');
		$module->title = $heading;
		$module->content("<form method=\"post\" style=\"display: inline;\">\n");
		$module->content("<input type=\"hidden\" name=\"response\" value=\"yes\" />\n");
		$module->content("<input type=\"hidden\" name=\"option\" value=\"$new_option\" />\n");
		$module->content("<input type=\"hidden\" name=\"action\" value=\"$new_action\" />\n");
		$module->content("<input type=\"hidden\" name=\"file\" value=\"$file\" />\n");
		$module->content("<input type=\"submit\" value=\"Yes\" />\n");
		$module->content("</form>\n");
		$module->content("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n");
		$module->content("<form method=\"post\" style=\"display: inline;\">\n");
		$module->content("<input type=\"hidden\" name=\"response\" value=\"no\" />\n");
		$module->content("<input type=\"hidden\" name=\"option\" value=\"$new_option\" />\n");
		$module->content("<input type=\"hidden\" name=\"action\" value=\"$new_action\" />\n");
		$module->content("<input type=\"hidden\" name=\"file\" value=\"$file\" />\n");
		$module->content("<input type=\"submit\" value=\"No\" />\n");
		$module->content("</form><br />\n");
	}
}

if (!function_exists('get_name')) {
    /**
     * Gets a name from a user.
     *
     * @param string $heading The heading to display.
     * @param string $new_option The option to pass the response to.
     * @param string $new_action The action to pass the response to.
     * @param string $orig_name The original name.
     * @param string $default The default value.
     * @deprecated
     */
	function get_name($heading, $new_option, $new_action, $orig_name = "", $default = "") {
		$module = new module('system', 'false', 'content');
		$module->title = $heading;
		$module->content("<form method=\"post\">\n");
		$module->content("<input type=\"text\" name=\"return_name\" value=\"$default\" />\n");
		$module->content("<input type=\"hidden\" name=\"option\" value=\"$new_option\" />\n");
		$module->content("<input type=\"hidden\" name=\"action\" value=\"$new_action\" />\n");
		$module->content("<input type=\"hidden\" name=\"orig_name\" value=\"$orig_name\" />\n");
		$module->content("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"submit\" value=\"Submit\" />\n");
		$module->content("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"button\" onclick=\"window.location='".$config->template->url()."';\" value=\"Cancel\" />\n");
		$module->content("</form><br />\n");
	}
}

?>
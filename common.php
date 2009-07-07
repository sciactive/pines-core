<?php
defined('D_RUN') or die('Direct access prohibited');

if ( isset($config->ability_manager) ) {
	$config->ability_manager->add('system', 'all', 'All Abilities', 'Let user do anything, regardless of whether they have the ability.');
}

/*
 * These are very rudamentary security functions. If you are worried about
 * security, I suggest replacing them.
 */
if (!function_exists('clean_checkbox')) {
	function clean_checkbox($name) {
		return str_replace('.', 'dot', urlencode($name));
	}
}

if (!function_exists('clean_filename')) {
	function clean_filename($filename) {
		return str_replace('..', 'fail-danger-dont-use-hack-attempt', $filename);
	}
}

if (!function_exists('is_clean_filename')) {
	function is_clean_filename($filename) {
		if ( strpos($filename, '..') === false ) {
			return true;
		} else {
			return false;
		}
	}
}

if (!function_exists('print_default')) {
	function print_default() {
		display_error("There is no installed component that has taken over the default print function!");
	}
}

if (!function_exists('display_error')) {
	function display_error($error_text) {
		global $page;
		$page->error($error_text);
	}
}

if (!function_exists('display_notice')) {
	function display_notice($notice_text) {
		global $page;
		$page->notice($notice_text);
	}
}

/*
 * The gatekeeper function should be defined by whatever component is taking
 * over user authentication. Standard practice is to return false if the user
 * is not logged in. If he is, gatekeeper should at least take an "ability"
 * argument which returns true if the user has the required permissions.
 */
if (!function_exists('gatekeeper')) {
	function gatekeeper() {
		return true;
	}
}

if (!function_exists('get_confirmation')) {
	function get_confirmation($heading, $new_option, $new_action, $file = "") {
		$module = new module('content');
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
	function get_name($heading, $new_option, $new_action, $orig_name = "", $default = "") {
		$module = new module('content');
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
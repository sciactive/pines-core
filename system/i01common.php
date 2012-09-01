<?php
/**
 * Common functions used in Pines.
 * 
 * These can be overriden by components, which is why this file starts with i01.
 * It's loaded along with the components' init files.
 *
 * @package Core
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/*
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
		return ( strpos($filename, '..') === false );
	}
}

?>
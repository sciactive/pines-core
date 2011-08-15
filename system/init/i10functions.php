<?php
/**
 * Define some basic functions.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
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
		if (!($return = scandir($directory, $sorting_order, $context))) return false;
	} else {
		if (!($return = scandir($directory, $sorting_order))) return false;
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

if (P_SCRIPT_TIMING) pines_print_time('Define Basic Functions');

?>
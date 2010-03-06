<?php
/**
 * Initialize the components.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/* ------ DEPRECATED ------ */
// Run the loaders for our components. This shouldn't require any sort of
// functionality, like entity or user management.
foreach ($pines->components as $cur_component) {
	if (substr($cur_component, 0, 4) == 'tpl_')
		continue;
	if ( file_exists("components/$cur_component/load.php") )
		include_once("components/$cur_component/load.php");
}
if (P_SCRIPT_TIMING) pines_print_time('Run Component Loaders');
/* ------ END ------ */

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
		return strcmp($a, $b);
	} else {
		return strcmp($str1, $str2);
	}
}
// Run the component init scripts.
$_p_cominit = glob('components/com_*/init/i*.php');
// Include the common system functions.
$_p_cominit[] = 'system/i20common.php';
// Sort by just the filename.
usort($_p_cominit, 'pines_sort_by_filename');
foreach ($_p_cominit as $_p_cur_cominit) {
	include($_p_cur_cominit);
}
if (P_SCRIPT_TIMING) pines_print_time('Init Components');

/* ------ DEPRECATED ------ */
// Load the common files. This should set up component-dependent things, such as
// abilities.
foreach ($pines->components as $cur_component) {
	if (substr($cur_component, 0, 4) == 'tpl_')
		continue;
	if ( file_exists("components/$cur_component/common.php") )
		include_once("components/$cur_component/common.php");
}
if (P_SCRIPT_TIMING) pines_print_time('Load Component Common');
/* ------ END ------ */

?>
<?php
/**
 * Initialize the components.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

// Run the component init scripts.
$_p_cominit = glob('components/com_*/init/i*.php');
// Include the common system functions.
$_p_cominit[] = 'system/i20common.php';
// Sort by just the filename.
usort($_p_cominit, 'pines_sort_by_filename');
foreach ($_p_cominit as $_p_cur_cominit) {
	/**
	 * Include each component init script in the correct order.
	 */
	if (P_SCRIPT_TIMING) pines_print_time("Init Script: $_p_cur_cominit");
	include($_p_cur_cominit);
	if (P_SCRIPT_TIMING) pines_print_time("Init Script: $_p_cur_cominit");
}

?>
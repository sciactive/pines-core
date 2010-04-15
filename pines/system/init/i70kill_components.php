<?php
/**
 * Kill the components.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (P_SCRIPT_TIMING) pines_print_time('Kill Components');
// Run the component kill scripts.
$_p_comkill = glob('components/com_*/init/k*.php');
// Sort by just the filename.
usort($_p_comkill, 'pines_sort_by_filename');
foreach ($_p_comkill as $_p_cur_comkill) {
	/**
	 * Include each component kill script in the correct order.
	 */
	if (P_SCRIPT_TIMING) pines_print_time("Kill Script: $_p_cur_comkill");
	include($_p_cur_comkill);
	if (P_SCRIPT_TIMING) pines_print_time("Kill Script: $_p_cur_comkill");
}
if (P_SCRIPT_TIMING) pines_print_time('Kill Components');

?>
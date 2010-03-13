<?php
/**
 * Process the menus.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

// Add the system menu.
$pines->menu->add_json_file('system/menu.json');
// Get the component menus.
$_p_commenus = glob('components/com_*/menu.json');
foreach ($_p_commenus as $_p_cur_commenus) {
	$pines->menu->add_json_file($_p_cur_commenus);
}
// Create and attach them.
$pines->menu->render();
if (P_SCRIPT_TIMING) pines_print_time('Process Menus');

?>
<?php
/**
 * Run the action.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/* Experimental code.
function test_conf_save_override(&$args, $hook, &$object) {
	var_dump($object->config);
	var_dump($object->defaults);
	var_dump($object->info);
	exit;
}

function test_configure_override(&$args) {
	if ($args[0] == 'com_configure' && $args[1] == 'save') {
		global $pines;
		$pines->hook->add_callback('configurator_component->save_config', -1, 'test_conf_save_override');
	}
}
$pines->hook->add_callback('$pines->action', -1, 'test_configure_override');
*/

if (P_SCRIPT_TIMING) pines_print_time('Run Requested Action');
// Call the action specified.
if ( $pines->action($pines->request_component, $pines->request_action) === 'error_404' ) {
	header('HTTP/1.0 404 Not Found', true, 404);
	$error_page = new module('system', 'error_404', 'content');
}
if (P_SCRIPT_TIMING) pines_print_time('Run Requested Action');

?>
<?php
/**
 * Run the action.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (P_SCRIPT_TIMING) pines_print_time('Run Requested Action');
// Call the action specified.
if ( action($pines->request_component, $pines->request_action) === 'error_404' ) {
	header('HTTP/1.0 404 Not Found', true, 404);
	$error_page = new module('system', 'error_404', 'content');
}
if (P_SCRIPT_TIMING) pines_print_time('Run Requested Action');

?>
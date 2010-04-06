<?php
/**
 * Render and output the page.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (P_SCRIPT_TIMING) pines_print_time('Render Page');
// Render the page.
echo $pines->page->render();
if (P_SCRIPT_TIMING) pines_print_time('Render Page', true);

?>
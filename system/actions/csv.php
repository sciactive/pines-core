<?php
/**
 * Export a CSV document.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper())
	punt_user('You don\'t have necessary permission.');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="'.$_REQUEST['filename'].'.csv" size='.strlen($_REQUEST['content']));

$pines->page->override = true;
$pines->page->override_doc($_REQUEST['content']);

?>
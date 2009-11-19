<?php
/**
 * Export a CSV document.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper()) {
	$config->user_manager->punt_user("You don't have necessary permission.");
	return;
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="'.$_REQUEST['filename'].'.csv" size='.strlen($_REQUEST['content']));

$page->override = true;
$page->override_doc($_REQUEST['content']);

?>
<?php
/**
 * Parse a time expression into a timestamp and export it.
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

$page->override = true;

try {
	if (empty($_REQUEST['timezone'])) {
		$date = new DateTime($_REQUEST['date']);
	} else {
		$date = new DateTime($_REQUEST['date'], new DateTimeZone($_REQUEST['timezone']));
	}
	$page->override_doc($date->format('U'));
} catch (Exception $e) {
	$page->override_doc('error');
}

?>
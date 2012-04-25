<?php
/**
 * Parse a time expression into a timestamp and export it.
 *
 * @package Core
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper())
	punt_user();

$pines->page->override = true;
header('Content-Type: text/plain');

try {
	if (empty($_REQUEST['timezone'])) {
		$date = new DateTime($_REQUEST['date']);
	} else {
		$date = new DateTime($_REQUEST['date'], new DateTimeZone($_REQUEST['timezone']));
	}
	$pines->page->override_doc($date->format('U'));
} catch (Exception $e) {
	$pines->page->override_doc('error');
}

?>
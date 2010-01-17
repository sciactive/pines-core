<?php
/**
 * Export a formatted date.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper())
	punt_user('You don\'t have necessary permission.');

$page->override = true;
$page->override_doc(pines_date_format(!is_numeric($_REQUEST['timestamp']) ? time() : (int) $_REQUEST['timestamp'], empty($_REQUEST['timezone']) ? null : new DateTimeZone($_REQUEST['timezone']), empty($_REQUEST['format']) ? 'Y-m-d H:i T' : (string) $_REQUEST['format']));

?>
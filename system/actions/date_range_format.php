<?php
/**
 * Export a formatted date range.
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
$pines->page->override_doc(format_date_range(!is_numeric($_REQUEST['start_timestamp']) ? time() : (int) $_REQUEST['start_timestamp'], !is_numeric($_REQUEST['end_timestamp']) ? time() : (int) $_REQUEST['end_timestamp'], empty($_REQUEST['format']) ? null : $_REQUEST['format'], empty($_REQUEST['timezone']) ? null : new DateTimeZone($_REQUEST['timezone'])));

?>
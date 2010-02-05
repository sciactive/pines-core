<?php
/**
 * A view to load the Pines Tags jQuery plugin.
 *
 * Attach this view if your module uses Pines Tags. It uses the default Pines
 * Tags theme.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

// Check to see if ptags is already included.
if (!$GLOBALS['js_ptags_included']) {
	echo ("<link href=\"{$pines->rela_location}system/css/jquery.ptags.default.css\" media=\"all\" rel=\"stylesheet\" type=\"text/css\" />\n");
	echo ("<script type=\"text/javascript\" src=\"{$pines->rela_location}system/js/jquery.ptags.js\"></script>\n");
	$GLOBALS['js_ptags_included'] = true;
}

?>
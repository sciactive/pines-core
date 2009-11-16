<?php
/**
 * A view to load the Tag Editor jQuery plugin.
 *
 * Attach this view if your module uses the Tag Editor.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

// Check to see if Tag Editor is already included.
if (!$GLOBALS['js_tageditor_included']) {
	echo ("<link href=\"{$config->rela_location}system/css/jquery.tag.editor.css\" media=\"all\" rel=\"stylesheet\" type=\"text/css\" />\n");
	echo ("<script type=\"text/javascript\" src=\"{$config->rela_location}system/js/jquery.tag.editor.js\"></script>\n");
	$GLOBALS['js_tageditor_included'] = true;
}

?>
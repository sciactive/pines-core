<?php
/**
 * A view to load the jsTree jQuery plugin.
 *
 * Attach this view if your module uses the jsTree.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

// Check to see if jsTree is already included.
if (!$GLOBALS['js_jstree_included']) {
    $page->head("<script type=\"text/javascript\" src=\"{$config->rela_location}system/js/jquery.tree.min.js\"></script>\n");
    $GLOBALS['js_jstree_included'] = true;
}

?>
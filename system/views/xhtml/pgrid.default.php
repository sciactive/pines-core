<?php
/**
 * A view to load the Pines Grid jQuery plugin.
 *
 * Attach this view if your module uses the Pines Grid. It uses the default
 * Pines Grid theme.
 *
 * Set "icons" to true if your grid's toolbar uses the default icons.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

// Check to see if the default icons are already included.
if (!$GLOBALS['pgrid_default_icons_included']) {
    if ($this->icons) $page->head("<link href=\"{$config->rela_location}system/css/jquery.pgrid.default.icons.css\" media=\"all\" rel=\"stylesheet\" type=\"text/css\" />\n");
    $GLOBALS['pgrid_default_icons_included'] = true;
}
// Check to see if pgrid is already included.
if (!$GLOBALS['pgrid_included']) {
    $page->head("<link href=\"{$config->rela_location}system/css/jquery.pgrid.default.css\" media=\"all\" rel=\"stylesheet\" type=\"text/css\" />\n");
    $page->head("<script type=\"text/javascript\" src=\"{$config->rela_location}system/js/jquery.pgrid.js\"></script>\n");
    $GLOBALS['pgrid_included'] = true;
}

return;
?>
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
	echo ("<script type=\"text/javascript\" src=\"{$pines->config->rela_location}system/js/jquery.tree/jquery.tree.min.js\"></script>\n");
	echo ("<script type=\"text/javascript\" src=\"{$pines->config->rela_location}system/js/jquery.tree/plugins/jquery.tree.contextmenu.js\"></script>\n");
	$GLOBALS['js_jstree_included'] = true;
}

?>
<script type="text/javascript">
	// <![CDATA[
	//function($){
	$.tree.defaults.ui.theme_path = "<?php echo $pines->config->rela_location; ?>system/css/jquery.tree/themes/default/style.css";
	//}(jQuery);
	// ]]>
</script>
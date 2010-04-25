<?php
/**
 * A view to load the jsTree jQuery plugin.
 *
 * Attach this view if your module uses the jsTree.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	// <![CDATA[
	<?php if (!$GLOBALS['js_jstree_included']) {
		$GLOBALS['js_jstree_included'] = true; ?>
	pines.loadjs("<?php echo $pines->config->rela_location; ?>system/js/jquery.tree/jquery.tree.min.js");
	pines.loadjs("<?php echo $pines->config->rela_location; ?>system/js/jquery.tree/plugins/jquery.tree.contextmenu.js");
	<?php } ?>
	$.tree.defaults.ui.theme_path = "<?php echo $pines->config->rela_location; ?>system/css/jquery.tree/themes/default/style.css";
	// ]]>
</script>
<?php
/**
 * Output a condition editor.
 * 
 * This view creates a grid where the user can add and edit conditions
 * (dependencies). The value is then stored in a hidden input. The default name
 * of the input is "conditions". It can be changed by setting the "input_name"
 * variable on the module.
 * 
 * Use the "conditions" variable to set the current conditions in the editor. It
 * should be an associative array with 'type' => 'value' associations.
 * 
 * You can also change the default grid height (300px) by setting the
 * "grid_height" variable.
 *
 * @package Core
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

if (!$pines->depend->check('component', 'com_pform&com_jquery&com_bootstrap&com_pgrid&com_markdown')) {
	echo 'The condition editor requires com_pform, com_jquery, com_bootstrap, com_pgrid, and com_markdown.';
	return;
}

$pines->com_pgrid->load();
?>
<div id="p_muid_editor" class="condition_editor">
	<script type="text/javascript">
		pines(function(){
			// Conditions
			var conditions = $("#p_muid_conditions"),
				conditions_table = $("#p_muid_table"),
				condition_dialog = $("#p_muid_dialog"),
				cur_condition = null;
			conditions_table.pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: 'Add Condition',
						extra_class: 'picon picon-document-new',
						selection_optional: true,
						click: function(){
							cur_condition = null;
							condition_dialog.dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Edit Condition',
						extra_class: 'picon picon-document-edit',
						double_click: true,
						click: function(e, rows){
							cur_condition = rows;
							condition_dialog.find("input[name=cur_condition_type]").val(pines.unsafe(rows.pgrid_get_value(1)))
							.end().find("input[name=cur_condition_value]").val(pines.unsafe(rows.pgrid_get_value(2)))
							.end().dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Remove Condition',
						extra_class: 'picon picon-edit-delete',
						click: function(e, rows){
							rows.pgrid_delete();
							update_conditions();
						}
					}
				],
				pgrid_view_height: <?php echo empty($this->grid_height) ? '"300px"' : json_encode($this->grid_height); ?>
			});

			// Condition Dialog
			condition_dialog.dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 500,
				buttons: {
					"Done": function(){
						var cur_condition_type = condition_dialog.find("input[name=cur_condition_type]").val(),
							cur_condition_value = condition_dialog.find("input[name=cur_condition_value]").val();
						if (cur_condition_type == "") {
							alert("Please provide a type for this condition.");
							return;
						}
						if (cur_condition == null) {
							// Is this a duplicate type?
							var dupe = false;
							conditions_table.pgrid_get_all_rows().each(function(){
								if (dupe) return;
								if ($(this).pgrid_get_value(1) == cur_condition_type)
									dupe = true;
							});
							if (dupe) {
								pines.notice('There is already a condition of that type.');
								return;
							}
							var new_condition = [{
								key: null,
								values: [
									pines.safe(cur_condition_type),
									pines.safe(cur_condition_value)
								]
							}];
							conditions_table.pgrid_add(new_condition);
						} else {
							cur_condition.pgrid_set_value(1, pines.safe(cur_condition_type));
							cur_condition.pgrid_set_value(2, pines.safe(cur_condition_value));
						}
						$(this).dialog('close');
					}
				},
				close: function(){
					update_conditions();
				}
			});

			var update_conditions = function(){
				condition_dialog.find("input[name=cur_condition_type]").val("")
				.end().find("input[name=cur_condition_value]").val("");
				conditions.val(JSON.stringify(conditions_table.pgrid_get_all_rows().pgrid_export_rows()));
			};

			update_conditions();

			condition_dialog.find("input[name=cur_condition_type]").autocomplete({
				"source": <?php echo (string) json_encode((array) array_keys($pines->depend->checkers)); ?>
			});
		});
	</script>
	<table class="conditions_table" id="p_muid_table">
		<thead>
			<tr>
				<th>Type</th>
				<th>Value</th>
			</tr>
		</thead>
		<tbody>
			<?php if (isset($this->conditions)) foreach ($this->conditions as $cur_key => $cur_value) { ?>
			<tr>
				<td><?php echo htmlspecialchars($cur_key); ?></td>
				<td><?php echo htmlspecialchars($cur_value); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<input type="hidden" name="<?php echo empty($this->input_name) ? 'conditions' : htmlspecialchars($this->input_name); ?>" id="p_muid_conditions" />
	<?php foreach (array_keys($pines->depend->checkers) as $cur_checker) {
		$checker_html = htmlspecialchars($cur_checker);
		$help = $pines->depend->help($cur_checker);
		if ($help) { ?>
	<div class="modal hide" id="p_muid_checker_<?php echo $checker_html; ?>">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 style="background-position: left center; background-repeat: no-repeat; min-height: 32px; padding-left: 36px; line-height: 32px;" class="picon-32 picon-help-contents"><?php echo htmlspecialchars($help['cname']); ?></h3>
		</div>
		<div class="modal-body">
			<?php if (!empty($help['description'])) { ?>
			<div><?php echo $pines->com_markdown->transform($help['description']); ?></div>
			<?php } if (!empty($help['syntax'])) { ?>
			<div class="page-header">
				<h3>Syntax</h3>
			</div>
			<div><?php echo $pines->com_markdown->transform($help['syntax']); ?></div>
			<?php } if (!empty($help['examples'])) { ?>
			<div class="page-header">
				<h3>Examples</h3>
			</div>
			<div><?php echo $pines->com_markdown->transform($help['examples']); ?></div>
			<?php } if ($help['simple_parse']) { ?>
			<div class="page-header">
				<h3>Simple Logic</h3>
			</div>
			<div>
				<p>This checker supports simple logic using the following
					constructs:</p>
				<ul>
					<li><code><abbr title="Exclamation Point">!</abbr></code> - Negation (the result is reversed)</li>
					<li><code><abbr title="Ampersand">&</abbr></code> - And (both values must be true)</li>
					<li><code><abbr title="Vertical Pipe">|</abbr></code> - Or (at least one value must be true)</li>
					<li><code><abbr title="Parentheses">()</abbr></code> - Grouping (test values in atomic groups)</li>
				</ul>
				<p>So to test value1 or value2, but not both, you could specify
					the following:</p>
				<pre><strong>(</strong>value1<strong>|</strong>value2<strong>)&!(</strong>value1<strong>&amp;</strong>value2<strong>)</strong></pre>
			</div>
			<?php } ?>
		</div>
		<div class="modal-footer">
			<button type="button" data-dismiss="modal" class="btn">Close</button>
		</div>
	</div>
		<?php }
	} ?>
	<div class="condition_dialog" id="p_muid_dialog" style="display: none;" title="Add a Condition">
		<div class="pf-form">
			<div class="pf-element">
				<span class="pf-label">Detected Types</span>
				<div class="pf-group">
					<div class="pf-field">These types were detected on this system. Click on a type to use it.</div>
				</div>
				<div><?php
				$checker_links = array();
				foreach (array_keys($pines->depend->checkers) as $cur_checker) {
					$checker_html = htmlspecialchars($cur_checker);
					$checker_js = htmlspecialchars(json_encode($cur_checker));
					$help = $pines->depend->help($cur_checker);
					$title = htmlspecialchars($help['cname']);
					if ($help)
						$checker_links[] = "<a title=\"$title\" href=\"javascript:void(0);\" onclick=\"\$('#p_muid_cur_condition_type').val($checker_js);\">$checker_html</a> <a href=\"#p_muid_checker_$checker_html\" data-toggle=\"modal\"><i class=\"icon-info-sign\"></i></a>";
					else
						$checker_links[] = "<a href=\"javascript:void(0);\" onclick=\"\$('#p_muid_cur_condition_type').val($checker_js);\">$checker_html</a>";
				}
				echo implode(', ', $checker_links);
				?></div>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Type</span>
					<input class="pf-field" type="text" name="cur_condition_type" id="p_muid_cur_condition_type" size="24" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Value</span>
					<input class="pf-field" type="text" name="cur_condition_value" size="24" /></label>
			</div>
		</div>
		<br style="clear: both; height: 1px;" />
	</div>
</div>
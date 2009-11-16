<?php
/**
 * module_group class.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A group of modules in a page position.
 *
 * This class is only used by the page controller to apply a model to the
 * content of the module group.
 *
 * @package Pines
 */
class module_group extends p_base {
	/**
	 * The module group's content.
	 * @var string $content
	 */
	public $content = '';

	/**
	 * Renders the content of the group.
	 *
	 * The group's template is found in the 'models' directory of the current
	 * template. If $model is set, it will looke for a file by that name (with
	 * .php appended and require it. If not, or the file deosn't exist, render()
	 * will require module_group.php. The module group's content variable
	 * ultimately ends up with the output from this file and is returned.
	 *
	 * @param string $model The model to use.
	 * @return string The rendered content.
	 */
	function render($model) {
		global $config;

		ob_start();

		// Return the content.
		ob_start();
		if (isset($model) && file_exists('templates/'.$config->current_template."/models/$model.php")) {
			require "templates/{$config->current_template}/models/$model.php";
		} else {
			require "templates/{$config->current_template}/models/module_group.php";
		}

		$this->content = ob_get_clean();
		return $this->content;
	}
}

?>
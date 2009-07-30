<?php
/**
 * The display controller for Pines. Handles ouput.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * The current template.
 * 
 * @global string $config->current_template
 */
$config->current_template = ( !empty($_REQUEST['template']) && $config->allow_template_override ) ?
	$_REQUEST['template'] : $config->default_template;
require_once('templates/'.$config->current_template.'/configure.php');

/*
class table {
	public $name, $id, $class, $style = '';
	public $table_array = array();

	/*
	 * $info can contain:
	 * type: th, td, or tf
	 * index: an integer
	 * name: a string
	 * id: a string
	 * class: a string
	 * style: a string
	 *
	function add_row($cells = array(), $info = array('type' => 'td')) {
		if ( isset($info['index']) ) {
			//TODO: finish this table class
		}
	}

	function render() {
		$return = '<table';
		$return .= empty($this->name) ? '' : ' name="'.$this->name.'"';
		$return .= empty($this->id) ? '' : ' id="'.$this->id.'"';
		$return .= empty($this->class) ? '' : ' class="'.$this->class.'"';
		$return .= empty($this->style) ? '' : ' style="'.$this->style.'"';
		$return .= ">\n";
	}
}
 */

/**
 * The page controller's variable. One of the few objects not under $config.
 * @global page $page
 */
$page = new page;

?>
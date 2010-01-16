<?php
/**
 * module class.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Modules are blocks of code or data to be placed on the page.
 * @package Pines
 */
class module extends p_base {
	/**
	 * The modules title.
	 * @var string $title
	 */
	public $title = '';
	/**
	 * A suffix to append to the module's class name.
	 *
	 * Applies to HTML modules.
	 * @var string $class_suffix
	 */
	public $class_suffix = '';
	/**
	 * The module's content.
	 *
	 * Though not necessary, this should be filled automatically using a view.
	 * @var string $content
	 */
	public $content = '';
	/**
	 * The component that the module will retrieve its content from.
	 * @var string $component
	 */
	public $component = '';
	/**
	 * The view that the module will retrieve its content from.
	 * @var string $view
	 */
	public $view = '';
	/**
	 * The position on the page to place the module.
	 * @var string $position
	 */
	public $position = null;
	/**
	 * The order the module will be placed in.
	 * @var int $order
	 */
	public $order = null;
	/**
	 * Whether the title of the module should be displayed.
	 * @var bool $show_title
	 */
	public $show_title = true;

	/**
	 * @param string $component
	 * @uses module::$component
	 * @param string $view
	 * @uses module::$view
	 * @param string $position
	 * @param int $order
	 * @uses module::attach()
	 * @return mixed If $position is given, returns the value of attach($position, $order).
	 */
	function __construct($component, $view, $position = null, $order = null) {
		$this->component = $component;
		$this->view = $view;
		if ( !is_null($position) ) {
			return $this->attach($position, $order);
		}
	}

	/**
	 * Attach the module to a position on the page.
	 *
	 * @global page Used to attach a module.
	 * @param string $position
	 * @uses module::$position
	 * @param int $order
	 * @uses module::$order
	 * @uses page::attach_module()
	 * @return int The order in which the module was placed.
	 */
	function attach($position, $order = null) {
		global $page;
		$this->position = $position;
		$this->order = $page->attach_module($this, $position, $order);
		return $this->order;
	}

	/**
	 * Detach the module from the page.
	 *
	 * @global page Used to detach a module.
	 * @uses page::detach_module()
	 * @return mixed The value of $page->detach_module.
	 */
	function detach() {
		global $page;
		return $page->detach_module($this, $this->position, $this->order);
	}

	/**
	 * Append content to the module.
	 *
	 * Note that this may be appended before the view is called, thus being
	 * placed before the content from the view.
	 *
	 * @param string $add_content Content to append.
	 */
	function content($add_content) {
		$this->content .= $add_content;
	}

	/**
	 * Retrieve the current content of the module.
	 *
	 * Note that this may not include the content generated by the view if not
	 * called late enough.
	 *
	 * @return string The content.
	 */
	function get_content() {
		return $this->content;
	}

	/**
	 * Renders the module.
	 *
	 * render() will first try to find the view in a folder named as the format
	 * defined in the template, then will remove text after and including the
	 * last dash in the format until it finds a view. If nothing is found after
	 * the last dash is removed, it will require() the view from the directory
	 * 'all'.
	 *
	 * For example, if the component is 'com_game' and the view is 'stats', and
	 * the templates type is 'xhtml-1.0-strict', render() will try the following
	 * files in order:
	 *
	 * components/com_game/views/xhtml-1.0-strict/stats.php
	 * components/com_game/views/xhtml-1.0/stats.php
	 * components/com_game/views/xhtml/stats.php
	 * components/com_game/views/all/stats.php
	 *
	 * The component 'system' has views in system/views/. The view 'null' in
	 * 'system' can be used as a blank view.
	 *
	 * The module's template is found in the 'models' directory of the current
	 * template. If $model is set, it will look for a file by that name (with
	 * .php appended and require it. If not, or the file doesn't exist, render()
	 * will require module.php. The module's content variable ultimately ends up
	 * with the output from this file and is returned.
	 *
	 * @param string $model The model to use.
	 * @return string The module's rendered content.
	 */
	function render($model) {
		global $config, $page;

		// Get content from the view.
		ob_start();
		$format = $config->template->format;
		while(true) {
			$filename = (($this->component != 'system') ? 'components/' : '').$this->component.'/views/'.$format.'/'.$this->view.'.php';
			if (file_exists($filename) || $format == 'all') {
				require $filename;
				break;
			} else {
				if (strrpos($format, '-') === false) {
					$format = 'all';
				} else {
					$format = substr($format, 0, strrpos($format, '-'));
				}
			}
		}
		$this->content(ob_get_clean());
		if (empty($this->content))
			return;

		// Return the content.
		ob_start();
		if (isset($model) && file_exists("templates/{$config->current_template}/models/$model.php")) {
			require "templates/{$config->current_template}/models/$model.php";
		} else {
			require "templates/{$config->current_template}/models/module.php";
		}
		$this->content = ob_get_clean();
		return $this->content;
	}
}

?>
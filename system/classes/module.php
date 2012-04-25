<?php
/**
 * module class.
 *
 * @package Core
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Page module.
 *
 * Modules are blocks of code or data to be placed on the page.
 *
 * @package Core
 */
class module {
	/**
	 * The module's unique ID.
	 *
	 * This unique ID is generated using mt_rand(). The random number is
	 * prefixed with "p_". It can be used to provide unique IDs for HTML and
	 * CSS, in order to prevent naming conflicts. See the content() function for
	 * information on using it.
	 *
	 * @var string
	 */
	public $muid;
	/**
	 * The module's title.
	 * @var string
	 */
	public $title = '';
	/**
	 * The module's note.
	 * @var string
	 */
	public $note = '';
	/**
	 * A list of additional classes to be added to the module.
	 *
	 * Applies to HTML modules.
	 * @var string
	 */
	public $classes = '';
	/**
	 * The module's content.
	 *
	 * Though not necessary, this should be filled automatically using a view.
	 * @var string
	 */
	public $content = '';
	/**
	 * The component that the module will retrieve its content from.
	 * @var string
	 */
	public $component = '';
	/**
	 * The view that the module will retrieve its content from.
	 * @var string
	 */
	public $view = '';
	/**
	 * The position on the page to place the module.
	 * @var string
	 */
	public $position = null;
	/**
	 * The order the module will be placed in.
	 * @var int
	 */
	public $order = null;
	/**
	 * Whether the title of the module should be displayed.
	 * @var bool
	 */
	public $show_title = true;
	/**
	 * Whether the module's content has been rendered.
	 * @access private
	 * @var bool
	 */
	private $is_rendered = false;
	/**
	 * Any data that is set on the module.
	 * @access private
	 * @var array
	 */
	private $data_container = array();

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
	public function __construct($component, $view, $position = null, $order = null) {
		$this->component = $component;
		$this->view = $view;
		$this->muid = 'p_'.mt_rand();
		if ( isset($position) )
			return $this->attach($position, $order);
	}

	/**
	 * Retrieve a variable.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 *
	 * @param string $name The name of the variable.
	 * @return mixed The value of the variable or nothing if it doesn't exist.
	 */
	public function &__get($name) {
		return $this->data_container[$name];
	}

	/**
	 * Checks whether a variable is set.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 *
	 * @param string $name The name of the variable.
	 * @return bool
	 * @todo Check that a referenced entity has not been deleted.
	 */
	public function __isset($name) {
		return isset($this->data_container[$name]);
	}

	/**
	 * Sets a variable.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 *
	 * @param string $name The name of the variable.
	 * @param string $value The value of the variable.
	 * @return mixed The value of the variable.
	 */
	public function __set($name, $value) {
		// Store the actual value passed.
		$save_value = $value;

		return ($this->data_container[$name] = $save_value);
	}

	/**
	 * Unsets a variable.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 *
	 * @param string $name The name of the variable.
	 */
	public function __unset($name) {
		unset($this->data_container[$name]);
	}

	/**
	 * Attach the module to a position on the page.
	 *
	 * @param string $position The position to place the module.
	 * @uses module::$position
	 * @param int $order The order in which to place the module.
	 * @uses module::$order
	 * @uses page::attach_module()
	 * @return int The order in which the module was placed.
	 */
	public function attach($position, $order = null) {
		global $pines;
		$this->position = $position;
		$this->order = $pines->page->attach_module($this, $position, $order);
		return $this->order;
	}

	/**
	 * Detach the module from the page.
	 *
	 * @uses page::detach_module()
	 * @return mixed The value of $pines->page->detach_module.
	 */
	public function detach() {
		global $pines;
		return $pines->page->detach_module($this, $this->position, $this->order);
	}

	/**
	 * Append content to the module.
	 *
	 * Any instance of the string "p_muid" will be replaced by the module's
	 * unique ID. You can use this to prevent naming collisions in HTML IDs and
	 * CSS classes.
	 *
	 * Note that the content may be appended before the view is called, thus
	 * being placed before the content from the view.
	 *
	 * @param string $add_content Content to append.
	 */
	public function content($add_content) {
		$this->content .= str_replace('p_muid', $this->muid, $add_content);
	}

	/**
	 * Retrieve the current content of the module.
	 *
	 * Note that this may not include the content generated by the view if not
	 * called late enough.
	 *
	 * @return string The content.
	 */
	public function get_content() {
		return $this->content;
	}

	/**
	 * Renders the module.
	 *
	 * render() will first try to find the view in a folder named as the format
	 * defined in the template, then will remove text after and including the
	 * last dash in the format until it finds a view. If nothing is found after
	 * the last dash is removed, it will include() the view from the directory
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
	 * Once the view is found, the module will include() it. Its output will be
	 * passed to the content() function.
	 *
	 * @return string The module's rendered content.
	 */
	public function render() {
		global $pines;

		// Is it already rendered?
		if ($this->is_rendered)
			return $this->content;

		// Get content from the view.
		ob_start();
		$format = $pines->template->format;
		$base_dir = ($this->component != 'system') ? 'components/' : '';
		while(true) {
			$filename = "{$base_dir}{$this->component}/views/{$format}/{$this->view}.php";
			if (file_exists($filename) || $format == 'all') {
				unset($format, $base_dir);
				/**
				 * This file should print the content of the module.
				 */
				include $filename;
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

		$this->is_rendered = true;
		$this->data_container = array();

		// Return the content.
		return $this->content;
	}

	/**
	 * Renders the module's code into a model.
	 *
	 * The module's template is found in the 'models' directory of the current
	 * template. If $model is set, it will look for a file by that name (with
	 * .php appended) and include() it. If not, or the file doesn't exist,
	 * render_model() will include() module.php.
	 *
	 * @param string $model The model to use.
	 * @return string The module's rendered content.
	 */
	public function render_model($model = 'module') {
		global $pines;

		// Render the module, if it's not already.
		if (!$this->is_rendered)
			$this->render();

		// Return the content.
		ob_start();
		if (isset($model) && file_exists("templates/{$pines->current_template}/models/$model.php")) {
			/**
			 * This file should print the module's content.
			 */
			include "templates/{$pines->current_template}/models/$model.php";
		} else {
			/**
			 * This file should print the module's content.
			 *
			 * It should always exist, in every template.
			 */
			include "templates/{$pines->current_template}/models/module.php";
		}
		$content = ob_get_clean();
		return $content;
	}
}

?>
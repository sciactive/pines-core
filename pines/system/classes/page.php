<?php
/**
 * page class.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * The controller of the page. It handles what is output to the user.
 * @package Pines
 */
class page extends p_base {
	/**
	 * The page's title.
	 * @var string $title
	 * @access protected
	 */
	protected $title = '';
	/**
	 * The content of the page.
	 * @var string $content
	 * @access protected
	 */
	protected $content = '';
	/**
	 * The content which will override the entire page.
	 * @var string $override_doc
	 * @access protected
	 */
	protected $override_doc = '';
	/**
	 * The notices to display.
	 * @var array $notice
	 * @access protected
	 */
	protected $notice = array();
	/**
	 * The errors to display.
	 * @var array $error
	 * @access protected
	 */
	protected $error = array();
	/**
	 * The moduels to display.
	 * @var array $modules
	 * @access protected
	 */
	protected $modules = array();
	/**
	 * Wether to override the output of the page and display custom content.
	 *
	 * @var bool $override
	 */
	public $override = false;
	
	/**
	 * Append text to the title of the page.
	 *
	 * @param string $add_title Text to append.
	 */
	public function title($add_title) {
		$this->title .= $add_title;
	}
	
	/**
	 * Get the title of the page.
	 *
	 * If the title has not been explicitly set, get_title() uses
	 * $pines->config->option_title.
	 *
	 * @return string The title.
	 */
	public function get_title() {
		global $pines;
		if ( !empty($this->title) ) {
			return $this->title;
		} else {
			return $pines->config->option_title;
		}
	}
	
	/**
	 * Add a notice to be displayed to the user.
	 *
	 * @param string $message The message text.
	 */
	public function notice($message) {
		$this->notice[] = $message;
	}
	
	/**
	 * Get the array of notices.
	 *
	 * @return array The array.
	 */
	public function get_notice() {
		return $this->notice;
	}
	
	/**
	 * Add an error to be displayed to the user.
	 *
	 * @param string $message The message text.
	 */
	public function error($message) {
		$this->error[] = $message;
	}
	
	/**
	 * Get the array of errors.
	 *
	 * @return array The array.
	 */
	public function get_error() {
		return $this->error;
	}
	
	/**
	 * Attach a module to a position on the page.
	 *
	 * The $order parameter is not guaranteed, and will be ignored if that place
	 * is already taken.
	 *
	 * @param module &$module The module to attach.
	 * @param string $position The position on the page. Templates can define their own positions.
	 * @param int $order The order in which to try to place the module.
	 * @return int The order in which the module was placed. This will be the last key + 1 if the desired order is already taken.
	 */
	public function attach_module(&$module, $position, $order = null) {
		if ( is_null($order) ) {
			if ( isset($this->modules[$position]) ) {
				end($this->modules[$position]);
				$order = key($this->modules[$position]) + 1;
			} else {
				$order = 0;
			}
		} else {
			if (isset($this->modules[$position])) {
				if ( isset($this->modules[$position][$order]) ) {
					end($this->modules[$position]);
					$order = key($this->modules[$position]) + 1;
				}
			}
		}
		$this->modules[$position][$order] = $module;
		return $order;
	}
	
	/**
	 * Deletes a module from the list of attached modules.
	 *
	 * It will try the module at $order or if $order is null then last one in
	 * $position, then iterate through $position searching for the module. It
	 * will delete the first match it finds, then stop and return true.
	 *
	 * @param module &$module The module to search for.
	 * @param string $position The position in which to search.
	 * @param int $order The order to try first.
	 * @return bool Whether a matching module was found and successfully deleted.
	 */
	public function detach_module(&$module, $position, $order = null) {
		if ( is_null($order) ) {
			if ( isset($this->modules[$position]) ) {
				end($this->modules[$position]);
				$order = key($this->modules[$position]);
			} else {
				return false;
			}
		}
		if ($this->modules[$position][$order] == $module) {
			unset($this->modules[$position][$order]);
			return true;
		} else {
			foreach ($this->modules[$position] as $key => $cur_module) {
				if ($this->modules[$position][$key] == $module) {
					unset($this->modules[$position][$key]);
					return true;
				}
			}
			return false;
		}
	}
	
	/**
	 * Append text to the override document.
	 *
	 * Use this function to supply output if you are overriding the document.
	 *
	 * @param string $add_body Text to append.
	 */
	public function override_doc($add_body) {
		$this->override_doc .= $add_body;
	}
	
	/**
	 * Get the override document.
	 *
	 * @return string The head section.
	 */
	public function get_override_doc() {
		return $this->override_doc;
	}
	
	/**
	 * Renders the page.
	 *
	 * It will require() the template.php file in the current template. However,
	 * render() will return the result of get_override_doc() if
	 * $pines->page->override is true.
	 *
	 * @global mixed Declare all globals in the function so they are available in the template.
	 * @uses page::$override
	 * @return string The page's rendered content.
	 */
	public function render() {
		// Make all globals accessible, so the template file can use them.
		foreach ($GLOBALS as $key => $val) { global $$key; }
		ob_start();
		if ( $this->override ) {
			echo $this->get_override_doc();
		} else {
			require("templates/{$pines->current_template}/template.php");
		}
		$this->content = ob_get_clean();
		return $this->content;
	}
	
	/**
	 * Render the modules in a position.
	 *
	 * @param string $position The position to work on.
	 * @param string $model The model to render the modules with.
	 * @uses module::render()
	 * @return string The content rendered by the modules.
	 */
	public function render_modules($position, $model = null) {
		$return = '';
		if (is_array($this->modules[$position])) {
			foreach ($this->modules[$position] as $cur_module) {
				$return .= $cur_module->render($model);
			}
		}
		return $return;
	}
}

?>

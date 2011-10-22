<?php
/**
 * page class.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * The controller of the page. It handles what is output to the user.
 * @package Pines
 */
class page {
	/**
	 * The page's title.
	 * @var string $title
	 * @access protected
	 */
	protected $title = '';
	/**
	 * Whether the default title has been loaded.
	 * @var bool $title_loaded
	 * @access private
	 */
	private $title_loaded = false;
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

	private function title_load() {
		global $pines;
		$this->title = $pines->config->page_title;
		$this->title_loaded = true;
	}

	/**
	 * Append text to the title of the page.
	 *
	 * @param string $add_title Text to append.
	 */
	public function title($add_title) {
		if (!$this->title_loaded)
			$this->title_load();
		$this->title .= $add_title;
	}

	/**
	 * Set the title of the page.
	 *
	 * @param string $new_title Title of the page.
	 */
	public function title_set($new_title) {
		if (!$this->title_loaded)
			$this->title_load();
		$this->title = $new_title;
	}

	/**
	 * Prepend text to the title of the page.
	 *
	 * @param string $add_title Text to prepend.
	 */
	public function title_pre($add_title) {
		if (!$this->title_loaded)
			$this->title_load();
		$this->title = $add_title.$this->title;
	}
	
	/**
	 * Get the title of the page.
	 *
	 * If the title has not been explicitly set, get_title() uses
	 * $pines->config->page_title.
	 *
	 * @return string The title.
	 */
	public function get_title() {
		if (!$this->title_loaded)
			$this->title_load();
		return $this->title;
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
		if ( !isset($order) ) {
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
		if ( !isset($order) ) {
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
	 * Declares the global $pines in the function so it is available in the
	 * template.
	 *
	 * @uses page::$override
	 * @return string The page's rendered content.
	 */
	public function render() {
		ob_start();
		if ( $this->override ) {
			echo $this->get_override_doc();
		} else {
			// Make $pines accessible, so the modules and template can use it.
			global $pines;
			foreach ($this->modules as &$cur_pos) {
				if (!$cur_pos || (array) $cur_pos !== $cur_pos)
					continue;
				foreach ($cur_pos as &$cur_module) {
					$cur_module->render();
				}
				unset($cur_module);
			}
			unset($cur_pos);
			/**
			 * This file should print the whole page's content.
			 */
			require "templates/{$pines->current_template}/template.php";
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
		if (isset($this->modules[$position])) {
			foreach ($this->modules[$position] as &$cur_module) {
				$return .= $cur_module->render_model($model);
			}
			unset($cur_module);
		}
		return $return;
	}
}

?>
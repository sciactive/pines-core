<?php
/**
 * Page class.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * The controller of the page. It controls what is output to the user.
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
     * The head section of the page.
     * @var string $head
     * @access protected
     */
	protected $head = '';
    /**
     * The body section of the page.
     * @var string $content
     * @access protected
     */
	protected $content = '';
    /**
     * The footer at the bottom of the page.
     * @var string $footer
     * @access protected
     */
	protected $footer = '';
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
     * The page's main menu.
     *
     * Used to navigate the system.
     *
     * @var menu $main_menu
     */
	public $main_menu = NULL;

    /**
     * Initialize the main menu.
     *
     * @access protected
     */
	public function __construct() {
		$this->main_menu = new menu;
	}

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
     * $config->option_title.
     *
     * @global DynamicConfig
     * @return string The title.
     */
	public function get_title() {
		global $config;
		if ( !empty($this->title) ) {
			return $this->title;
		} else {
			return $config->option_title;
		}
	}

    /**
     * Append text to the head section of the page.
     *
     * @param string $add_head Text to append.
     */
	public function head($add_head) {
		$this->head .= $add_head;
	}

    /**
     * Get the head section of the page.
     *
     * @return string The head section.
     */
	public function get_head() {
		return $this->head;
	}

    /**
     * Add a notice to be displayed to the user.
     *
     * @param string $message The message text.
     * @param string $image The filename of an image to use.
     * @todo Image support.
     */
	public function notice($message, $image = NULL) {
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
     * @param string $image The filename of an image to use.
     * @todo Image support.
     */
	public function error($message, $image = NULL) {
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
     * Append text to the body of the page.
     * 
     * This may not be supported by some templates, so try to avoid using it. It
     * may also appear in any part of the body. You can use a module with the
     * system/false view instead.
     *
     * @param string $add_content Text to append.
     */
	public function content($add_content) {
		$this->content .= $add_content;
	}

    /**
     * Get the text appended to the body of the page.
     *
     * @return string The body text.
     */
	public function get_content() {
		return $this->content;
	}

    /**
     * Attach a module to a position on the page.
     * 
     * The $order parameter is not guaranteed, and will be ignored if that place
     * is already taken.
     *
     * @param module $module The module to attach.
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
     * @param module $module The module to search for.
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
     * Append text to the footer of the page.
     *
     * @param string $add_footer Text to append.
     */
	public function footer($add_footer) {
		$this->footer .= $add_footer;
	}

    /**
     * Get the footer of the page.
     *
     * @return string The footer.
     */
	public function get_footer() {
		return $this->footer;
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
     * Render the page.
     *
     * It will first render all the modules, then require() the template.php
     * file in the current template. However, render() will display the result
     * of get_override_doc() if $page->override is true.
     *
     * @global mixed Declare all globals in the function so they are available in the template.
     * @uses page::$override
     */
	public function render() {
        // Render each module. This will fill in the head section of the page.
        foreach ($this->modules as $cur_position) {
            foreach ($cur_position as $cur_module) {
                $cur_module->render();
            }
        }

        // Make all globals accessible, so the template file can use them.
		foreach ($GLOBALS as $key => $val) { global $$key; }
		if ( $this->override ) {
			echo $this->get_override_doc();
		} else {
			require("templates/".$config->current_template."/template.php");
		}
	}

    /**
     * Render the modules in a position.
     *
     * @param string $position The position to work on.
     * @return string The content rendered by the modules.
     */
    public function render_modules($position) {
        $return = '';
		foreach ($this->modules[$position] as $cur_module) {
			$return .= $cur_module->get_content() . "\n";
		}
        return $return;
    }
}

?>

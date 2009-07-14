<?php
defined('D_RUN') or die('Direct access prohibited');

$config->current_template = ( !empty($_REQUEST['template']) && $config->allow_template_override ) ?
	$_REQUEST['template'] : $config->default_template;
require_once('templates/'.$config->current_template.'/configure.php');

class module {
	public $title, $class_suffix, $content, $component, $view = '';
    public $position, $order = null;
	public $show_title = true;

	function __construct($component, $view, $position = null, $order = null) {
        $this->component = $component;
        $this->view = $view;
		if ( !is_null($position) ) {
			global $page;
			return $this->attach($position, $order);
		}
	}

	function attach($position, $order = null) {
		global $page;
        $this->position = $position;
        $this->order = $page->attach_module($this, $position, $order);
		return $this->order;
	}

    function detach() {
		global $page;
		return $page->detach_module($this, $this->position, $this->order);
    }

	function content($add_content) {
		$this->content .= $add_content;
	}

	function get_content() {
		return $this->content;
	}

	function render() {
        global $config, $page;
        
        // Get content from the view.
        ob_start();
        require (($this->component != 'system') ? 'components/' : '').$this->component.'/views/'.$config->template->format.'/'.$this->view.'.php';
        $this->content(ob_get_contents());
        ob_end_clean();

        // Return the content.
        ob_start();
        require 'templates/'.$config->current_template.'/models/module.php';
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
	}
}

class page {
	protected $title, $head, $content, $footer, $override_doc = '';
	protected $notice, $error, $modules = array();
	public $override = false;
	public $main_menu = NULL;

	function __construct() {
		$this->main_menu = new menu;
	}

	function title($add_title) {
		$this->title .= $add_title;
	}

	function get_title() {
		global $config;
		if ( !empty($this->title) ) {
			return $this->title;
		} else {
			return $config->option_title;
		}
	}

	function head($add_head) {
		$this->head .= $add_head;
	}

	function get_head() {
		return $this->head;
	}

	//TODO: image support.
	function notice($message, $image = NULL) {
		$this->notice[] = $message;
	}

	function get_notice() {
		return $this->notice;
	}

	//TODO: image support.
	function error($message, $image = NULL) {
		$this->error[] = $message;
	}

	function get_error() {
		return $this->error;
	}

	// Don't use these unless you need to write above all the modules!
	function content($add_content) {
		$this->content .= $add_content;
	}

	function get_content() {
		return $this->content;
	}

	function attach_module(&$module, $position, $order = null) {
		if ( is_null($order) ) {
			if ( isset($this->modules[$position]) ) {
				$order = count($this->modules[$position]);
			} else {
				$order = 0;
			}
		} else {
			if (isset($this->modules[$position])) {
				if ( isset($this->modules[$position][$order]) )
					$order = count($this->modules[$position]);
			}
		}
		$this->modules[$position][$order] = $module;
		return $order;
	}

    /*
     * Deletes a module from the list of attached modules. Will try the one at
     * $order or if $order is null then last one in $position, then iterate
     * through $position searching for the module. It will delete the first
     * match it finds, then stop and return true. If it finds none, it will
     * return false.
     */
	function detach_module(&$module, $position, $order = null) {
		if ( is_null($order) ) {
			if ( isset($this->modules[$position]) ) {
				$order = count($this->modules[$position]);
			} else {
				$order = 0;
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

	function footer($add_footer) {
		$this->footer .= $add_footer;
	}

	function get_footer() {
		return $this->footer;
	}

	function override_doc($add_body) {
		$this->override_doc .= $add_body;
	}

	function get_override_doc() {
		return $this->override_doc;
	}

	function render() {
		foreach ($GLOBALS as $key => $val) { global $$key; }
		if ( $this->override ) {
			echo $this->get_override_doc();
		} else {
			require("templates/".$config->current_template."/template.php");
		}
	}
}

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
	 */
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

class menu {
	public $menu = array();

	/*
	 * Add an item to the menu.
	 * Returns the ID of the item, possible to use as the father of another.
	 */
	function add($name, $data = '#', $father = NULL, $id = NULL) {
		if ( is_null($id) )
			$id = count($this->menu);
		$this->menu[$id]['name'] = $name;
		$this->menu[$id]['data'] = $data;
		$this->menu[$id]['father'] = $father;
		return $id;
	}

	/*
	 * Renders the menu's HTML.
	 * Returns the HTML.
	 */
	function render($top_container = array('<ul class="dropdown dropdown-horizontal">', '</ul>'), $top_element = array('<li>', '</li>'), $sub_container = array('<ul>', '</ul>'), $sub_element = array('<li>', '</li>'), $link = '<a href="#DATA#">#NAME#</a>', $post_html = '<hr style="visibility: hidden; clear: both;" />') {
		$return = '';
		if ( empty($this->menu) ) return $return;
		$return .= $top_container[0];
		foreach ($this->menu as $cur_id => $cur_item) {
			if ( is_null($cur_item['father']) ) {
				$return .= $top_element[0];
				$cur_link = str_replace('#DATA#', $cur_item['data'], $link);
				$cur_link = str_replace('#NAME#', $cur_item['name'], $cur_link);
				$return .= $cur_link;
				$return .= $this->render_item($cur_id, $sub_container, $sub_element, $link);
				$return .= $top_element[1];
			}
		}
		$return .= $top_container[1];
		$return .= $post_html;
		return $return;
	}

	function render_item($id, $sub_container, $sub_element, $link) {
		$return = '';
		foreach ($this->menu as $cur_id => $cur_item) {
			if ( $cur_item['father'] === $id ) {
				$return .= $sub_element[0];
				$cur_link = str_replace('#DATA#', $cur_item['data'], $link);
				$cur_link = str_replace('#NAME#', $cur_item['name'], $cur_link);
				$return .= $cur_link;
				$return .= $this->render_item($cur_id, $sub_container, $sub_element, $link);
				$return .= $sub_element[1];
			}
		}
		if ( !empty($return) )
		$return = $sub_container[0] . $return . $sub_container[1];
		return $return;
	}

	function orphans() {
		$return = array();
		foreach ($this->menu as $cur_id => $cur_item) {
			if ( !is_null($cur_item['father']) && !isset($this->menu[$cur_item['father']]) ) {
				$return[$cur_id] = $cur_item;
			}
		}
		return $return;
	}
}

$page = new page;

?>
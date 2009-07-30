<?php
/**
 * Menu class.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A menu.
 * @package Pines
 */
class menu {
    /**
     * The menu's array of entries.
     *
     * @var array
     */
	public $menu = array();

    /**
     * Add an item to or overwrite an entry in the menu.
     *
     * @param string $name The name of the entry.
     * @param string $data The data of the entry. Usually this is the URL to which the entry will point.
     * @param int $father The parent entry.
     * @param int $id The ID of the entry. This should only be set if you are overwriting another entry.
     * @return int The ID of the new entry.
     */
	function add($name, $data = '#', $father = NULL, $id = NULL) {
		if ( is_null($id) )
			$id = count($this->menu);
		$this->menu[$id]['name'] = $name;
		$this->menu[$id]['data'] = $data;
		$this->menu[$id]['father'] = $father;
		return $id;
	}

    /**
     * Renders the menu.
     *
     * @param array $top_container The containing element of the menu.
     * @param array $top_element The element of each toplevel entry.
     * @param array $sub_container The containing element of each sublevel entry in the menu.
     * @param array $sub_element The element of each sublevel entry.
     * @param string $link The link code for each entry. The text "#NAME#" and "#DATA#" will be replaced by the name and data of the entry respectively.
     * @param string $post_code Any code which will be appended to the completed menu.
     * @uses menu::render_item()
     * @return string The rendered code.
     */
	function render($top_container = array('<ul class="dropdown dropdown-horizontal">', '</ul>'), $top_element = array('<li>', '</li>'), $sub_container = array('<ul>', '</ul>'), $sub_element = array('<li>', '</li>'), $link = '<a href="#DATA#">#NAME#</a>', $post_code = '<hr style="visibility: hidden; clear: both;" />') {
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
		$return .= $post_code;
		return $return;
	}

    /**
     * Render an entry (and children) of the menu.
     *
     * @param int $id The entry's ID.
     * @param array $sub_container The containing element of each entry.
     * @param array $sub_element The element of each entry.
     * @param string $link The link code for each entry. The text "#NAME#" and "#DATA#" will be replaced by the name and data of the entry respectively.
     * @return string The rendered entry.
     */
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

    /**
     * Find all the orphaned entries in the menu.
     *
     * @return array An array of entries.
     */
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

?>

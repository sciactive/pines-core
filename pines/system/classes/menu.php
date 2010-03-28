<?php
/**
 * menu class.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * The Pines menu system.
 * @package Pines
 */
class menu extends p_base {
	/**
	 * Array of specially formatted menu entries.
	 * @var array
	 */
	public $menu_arrays = array();

	/**
	 * Read JSON formatted menu data from a file.
	 *
	 * All menu entries are added to $this->menu_arrays.
	 *
	 * @param string $filename The path of the file to read.
	 * @return bool True on success, false on failure.
	 */
	public function add_json_file($filename) {
		$array = json_decode(file_get_contents($filename), true);
		if (!is_array($array))
			return false;
		$this->menu_arrays = array_merge($this->menu_arrays, $array);
		return true;
	}

	/**
	 * Parse the entries and build the menus.
	 *
	 * render() will read and process menu dependencies using
	 * $pines->depend->check(), and add each menu to a module in its proper
	 * position. It will then call $pines->template->menu() and give the menu
	 * array as an object for each menu. It is the current template's job to
	 * return the code that gets placed into the module's content.
	 *
	 * This is an example of the array passed to $pines->template->menu():
	 *
	 * <pre>
	 * Array (
	 *     [0] => Array (
	 *             [text] => Main Menu
	 *             [position] => main_menu
	 *         )
	 *     [com_configure] => Array (
	 *             [0] => Array (
	 *                     [text] => Configuration
	 *                 )
	 *             [components] => Array (
	 *                     [0] => Array (
	 *                             [text] => Components
	 *                             [href] => /pines/index.php/configure/list/
	 *                         )
	 *                 )
	 *         )
	 *     [myaccount] => Array (
	 *             [0] => Array (
	 *                     [text] => My Account
	 *                     [href] => /pines/index.php/user/editself/
	 *                 )
	 *         )
	 *     [logout] => Array (
	 *             [0] => Array (
	 *                     [text] => Logout
	 *                     [href] => /pines/index.php/user/logout/
	 *                     [onclick] => return confirm("Are you sure?");
	 *                 )
	 *         )
	 * )
	 * </pre>
	 */
	public function render() {
		global $pines;
		$menus = array();
		// Go through each entry and organize them into a multidimensional
		// array.
		foreach ($this->menu_arrays as $cur_entry) {
			$tmp_path = explode('/', $cur_entry['path']);
			$cur_menus =& $menus;
			do {
				if (!key_exists($tmp_path[0], $cur_menus))
					$cur_menus[$tmp_path[0]] = array();
				$cur_menus =& $cur_menus[$tmp_path[0]];
				$tmp_path = array_slice($tmp_path, 1);
			} while (count($tmp_path));
			$cur_menus[0] = $cur_entry;
		}

		// Clean up the full menu.
		$this->cleanup($menus);

		foreach ($menus as $cur_menu) {
			$module = new module('system', 'null', $cur_menu[0]['position']);
			if (isset($cur_menu[0]['text']))
				$module->title = $cur_menu[0]['text'];
			$module->content($pines->template->menu($cur_menu));
		}
	}

	/**
	 * Clean up the menu array.
	 *
	 * cleanup() will remove entries whose dependencies aren't met, and call
	 * $pines->template->url with the parameters found in ['href'] variables,
	 * if they are an array. It checks the "children" dependency for menus that
	 * require children. Set it to true to remove the entry if it has no
	 * children. It will also remove ['path'] and ['depend'].
	 *
	 * @access private
	 * @param array $array The menu array.
	 * @param bool $is_top_menu Allow an array without a menu entry item.
	 * @return bool True if the entry passes all tests, false otherwise.
	 */
	private function cleanup(&$array, $is_top_menu = true) {
		global $pines;
		// If this isn't a top menu, and has no menu entry, return false.
		if (!$is_top_menu && !$array[0])
			return false;
		// Check all the dependencies. If any are not met, return false.
		if ($array[0]['depend']) {
			foreach ($array[0]['depend'] as $key => $value) {
				if ($key == 'children')
					continue;
				if (!$pines->depend->check($key, $value))
					return false;
			}
		}
		// Transform URL arrays into actual URLs.
		if ($array[0]['href'] && is_array($array[0]['href']))
			$array[0]['href'] = call_user_func_array(array($pines->template, 'url'), $array[0]['href']);
		// Clean up all its children.
		foreach ($array as $key => &$value) {
			if (!is_int($key) && !$this->cleanup($value, false))
				unset($array[$key]);
		}
		// If the menu requires children and has none, return false.
		if ($array[0]['depend']['children'] && count($array) == 1)
			return false;
		unset($array[0]['path']);
		unset($array[0]['depend']);
		return true;
	}
}

?>

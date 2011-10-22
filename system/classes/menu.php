<?php
/**
 * menu class.
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
 * The Pines menu system.
 * @package Pines
 */
class menu {
	/**
	 * Array of specially formatted menu entries.
	 * @var array
	 */
	public $menu_arrays = array();
	/**
	 * The hierarchical menu array.
	 * @access private
	 * @var array
	 */
	private $menus = array();

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
		if ((array) $array !== $array)
			return false;
		foreach ($array as $value) {
			$this->menu_arrays[] = $value;
		}
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
	 * It will remove entries whose dependencies aren't met and ['path'], and
	 * call $pines->template->url with the parameters found in ['href']
	 * variables, if they are an array.
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
		// Go through each entry and organize them into a multidimensional
		// array.
		foreach ($this->menu_arrays as &$cur_entry) {
			if ($cur_entry['depend']) {
				if ($notmet)
					$notmet = false;
				foreach ($cur_entry['depend'] as $key => &$value) {
					if ($key == 'children')
						continue;
					if (!$pines->depend->check($key, $value)) {
						$notmet = true;
						break;
					}
				}
				unset($value);
				if ($notmet)
					continue;
			}
			// Transform URL arrays into actual URLs.
			if (isset($cur_entry['href']) && (array) $cur_entry['href'] === $cur_entry['href'])
				$cur_entry['href'] = call_user_func_array(array($pines->template, 'url'), $cur_entry['href']);
			$tmp_path = explode('/', $cur_entry['path']);
			unset($cur_entry['path']);
			$cur_menus =& $this->menus;
			foreach ($tmp_path as $cur_path) {
				if (!isset($cur_menus[$cur_path]))
					$cur_menus[$cur_path] = array();
				$cur_menus =& $cur_menus[$cur_path];
			}
			/* which way is faster?
			do {
				if (!isset($cur_menus[$tmp_path[0]]))
					$cur_menus[$tmp_path[0]] = array();
				$cur_menus =& $cur_menus[$tmp_path[0]];
				$tmp_path = array_slice($tmp_path, 1);
			} while (count($tmp_path));
			 */
			$cur_menus[0] = $cur_entry;
		}
		unset($cur_entry);
		$this->menu_arrays = array();

		// Clean up the full menu.
		$this->cleanup($this->menus);

		foreach ($this->menus as &$cur_menu) {
			$module = new module('system', 'null', $cur_menu[0]['position']);
			if (isset($cur_menu[0]['text']))
				$module->title = $cur_menu[0]['text'];
			$module->content($pines->template->menu($cur_menu));
		}
	}

	/**
	 * Clean up the menu array.
	 *
	 * cleanup() checks the "children" dependency for menus that
	 * require children. Set it to true to remove the entry if it has no
	 * children. It will also remove ['depend'].
	 *
	 * @access private
	 * @param array $array The menu array.
	 * @param bool $is_top_menu Allow an array without a menu entry item.
	 * @return bool True if the entry passes all tests, false otherwise.
	 */
	private function cleanup(&$array, $is_top_menu = true) {
		// If this isn't a top menu, and has no menu entry, return false.
		if (!$is_top_menu && !$array[0])
			return false;
		/* What's faster?
		$count = count($array);
		if ($count > 1) {
			// Clean up all its children.
			foreach ($array as $key => &$value) {
				if ($key === 0)
					continue;
				if (!$this->cleanup($value, false)) {
					unset($array[$key]);
					$count--;
			}
			}
			// If the menu requires children and has none, return false.
			if ($array[0]['depend']['children'] && $count === 1)
				return false;
		*/
		if (($array !== array($array[0]))) {
			// Clean up all its children.
			foreach ($array as $key => &$value) {
				if ($key === 0)
					continue;
				if (!$this->cleanup($value, false))
					unset($array[$key]);
			}
			// If the menu requires children and has none, return false.
			if ($array[0]['depend']['children'] && ($array === array($array[0])))
				return false;
			// If the menu should be sorted, sort by its keys.
			if ($array[0]['sort'])
				ksort($array);
		} else {
			// It has no children.
			if ($array[0]['depend']['children'])
				return false;
		}
		unset($array[0]['depend']);
		return true;
	}
}

?>

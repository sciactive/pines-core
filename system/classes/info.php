<?php
/**
 * info class.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A dynamic info class.
 *
 * Components' info will be loaded into their variables. In other words, when
 * you access $pines->info->com_xmlparser->version, if
 * $pines->info->com_xmlparser is empty, the info file for com_xmlparser class
 * will attempt to be loaded into it.
 *
 * The "template" variable will load the current template's info.
 *
 * @package Pines
 */
class info {
	/**
	 * Fill this object with system info.
	 */
	public function __construct() {
		$info_array = require('system/info.php');
		foreach ($info_array as $key => $value) {
			$this->$key = $value;
		}
	}

	/**
	 * Retrieve a variable.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 *
	 * This function will try to load a component's info into any variables
	 * beginning with com_ or tpl_.
	 *
	 * @param string $name The name of the variable.
	 * @return mixed The value of the variable or nothing if it doesn't exist.
	 */
	public function &__get($name) {
		global $pines;
		if ($name == 'template') {
			$name = $pines->current_template;
			$is_template = true;
		}
		if (substr($name, 0, 4) == 'com_') {
			// Load the info for a component.
			if ( file_exists("components/$name/info.php") ) {
				$info_array = include("components/$name/info.php");
				if ((array) $info_array === $info_array) {
					$this->$name = (object) array();
					foreach ($info_array as $key => $value) {
						$this->$name->$key = $value;
					}
				}
			}
		} elseif (substr($name, 0, 4) == 'tpl_') {
			// Load the info for a template.
			if ( file_exists("templates/$name/info.php") ) {
				$info_array = include("templates/$name/info.php");
				if ((array) $info_array === $info_array) {
					$this->$name = (object) array();
					foreach ($info_array as $key => $value) {
						$this->$name->$key = $value;
					}
				}
			}
		}
		if (isset($is_template) && $is_template)
			$this->template =& $this->$name;
		return $this->$name;
	}

	/**
	 * Checks whether a variable is set.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 *
	 * This functions checks whether info can be loaded for a component.
	 *
	 * @param string $name The name of the variable.
	 * @return bool
	 */
	public function __isset($name) {
		global $pines;
		if (substr($name, 0, 4) == 'com_') {
			if ( file_exists("components/$name/info.php") ) {
				$info_array = include("components/$name/info.php");
				return ((array) $info_array === $info_array);
			}
		} elseif (substr($name, 0, 4) == 'tpl_') {
			if ( file_exists("templates/$name/info.php") ) {
				$info_array = include("templates/$name/info.php");
				return ((array) $info_array === $info_array);
			}
		}
		return false;
	}
}

?>
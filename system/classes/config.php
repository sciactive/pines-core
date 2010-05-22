<?php
/**
 * config class.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A dynamic config class.
 *
 * Components' configuration will be loaded into their variables. In other
 * words, when you access $pines->config->com_xmlparser->strict, if
 * $pines->config->com_xmlparser is empty, the configuration file for
 * com_xmlparser class will attempt to be loaded into it.
 *
 * The "template" variable will load the current template's configuration.
 *
 * @package Pines
 */
class config extends p_base {
	/**
	 * Fill this object with system configuration.
	 */
	public function __construct() {
		$config_array = require('system/defaults.php');
		$this->fill_object($config_array, $this);
		if (file_exists('system/config.php')) {
			$config_array = require('system/config.php');
			$this->fill_object($config_array, $this);
		}
	}

	/**
	 * Retrieve a variable.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 *
	 * This function will try to load a component's configuration into any
	 * variables beginning with com_ or tpl_.
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
			// Load the config for a component.
			if ( file_exists("components/$name/defaults.php") ) {
				$config_array = include("components/$name/defaults.php");
				if (is_array($config_array)) {
					$this->$name = new p_base;
					$this->fill_object($config_array, $this->$name);
					if ( file_exists("components/$name/config.php") ) {
						$config_array = include("components/$name/config.php");
						$this->fill_object($config_array, $this->$name);
					}
				}
			}
		} elseif (substr($name, 0, 4) == 'tpl_') {
			// Load the config for a template.
			if ( file_exists("templates/$name/defaults.php") ) {
				$config_array = include("templates/$name/defaults.php");
				if (is_array($config_array)) {
					$this->$name = new p_base;
					$this->fill_object($config_array, $this->$name);
					if ( file_exists("templates/$name/config.php") ) {
						$config_array = include("templates/$name/config.php");
						$this->fill_object($config_array, $this->$name);
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
	 * This functions checks whether configuration can be loaded for a
	 * component.
	 *
	 * @param string $name The name of the variable.
	 * @return bool
	 */
	public function __isset($name) {
		global $pines;
		if (substr($name, 0, 4) == 'com_') {
			if ( file_exists("components/$name/defaults.php") ) {
				$config_array = include("components/$name/defaults.php");
				return is_array($config_array);
			}
		} elseif (substr($name, 0, 4) == 'tpl_') {
			if ( file_exists("templates/$name/defaults.php") ) {
				$config_array = include("templates/$name/defaults.php");
				return is_array($config_array);
			}
		}
		return false;
	}

	/**
	 * Gets an array of the components which can be a default component.
	 *
	 * The way a component can be a default component is to have a "default"
	 * action, which loads what the user will see when they first log on.
	 *
	 * @return array An array of component names.
	 */
	public function get_default_components() {
		global $pines;
		$return = array();
		foreach ($pines->components as $cur_component) {
			if ( file_exists("components/{$cur_component}/actions/default.php") )
				$return[] = $cur_component;
		}
		return $return;
	}

	/**
	 * Fill an object with the data from a configuration array.
	 *
	 * The configuration array must be formatted correctly. It must contain one
	 * array per variable, each with at least the following items:
	 *
	 * - name - The variable's name.
	 * - cname - A common name for the variable. (A title)
	 * - value - The variable's value.
	 *
	 * @param array $config_array The configuration array to process.
	 * @param mixed &$object The object to which the variables should be added.
	 * @return bool True on success, false on failure.
	 */
	public function fill_object($config_array, &$object) {
		if (!is_array($config_array)) return false;
		foreach ($config_array as $cur_var) {
			$name = $cur_var['name'];
			$value = $cur_var['value'];
			$object->$name = $value;
		}
		return true;
	}
}

?>
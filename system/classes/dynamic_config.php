<?php
/**
 * dynamic_config class.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A dynamic config object.
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
class dynamic_config extends p_base {
	/**
	 * Fill this object with system configuration.
	 */
	public function __construct() {
		$config_array = require('system/configure.php');
		$this->fill_object($config_array, $this);
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
		if (substr($name, 0, 4) == 'com_') {
			// Load the config for a component.
			if ( file_exists("components/$name/configure.php") ) {
				$config_array = include("components/$name/configure.php");
				if (is_array($config_array)) {
					$this->$name = new p_base;
					$this->fill_object($config_array, $this->$name);
				}
			}
		} elseif (substr($name, 0, 4) == 'tpl_') {
			// Load the config for a template.
			if ( file_exists("templates/$name/configure.php") ) {
				$config_array = include("templates/$name/configure.php");
				if (is_array($config_array)) {
					$this->$name = new p_base;
					$this->fill_object($config_array, $this->$name);
				}
			}
		}
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
		if (substr($name, 0, 4) == 'com_') {
			if ( file_exists("components/$name/configure.php") ) {
				$config_array = include("components/$name/configure.php");
				return is_array($config_array);
			}
		} elseif (substr($name, 0, 4) == 'tpl_') {
			if ( file_exists("templates/$name/configure.php") ) {
				$config_array = include("templates/$name/configure.php");
				return is_array($config_array);
			}
		}
		return false;
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
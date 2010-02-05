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
 * A dynamic loading object.
 *
 * Component classes will be automatically loaded into their variables beginning
 * with run_. In other words, when you call $pines->run_xmlparser->parse(), if
 * $pines->run_xmlparser is empty, the com_xmlparser class will attempt to be
 * loaded for it.
 *
 * @package Pines
 */
class dynamic_config extends p_base {
	var $standard_classes = array();
	/**
	 * Retrieve a variable.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 * 
	 * This function will try to load a component's class into any variables
	 * beginning with run_.
	 *
	 * @param string $name The name of the variable.
	 * @return mixed The value of the variable or nothing if it doesn't exist.
	 */
	public function &__get($name) {
		if (preg_match('/^run_/', $name)) {
			global $pines;
			$new_name = preg_replace('/^run_/', 'com_', $name);
			try {
				$this->$name = new $new_name;
				$pines->hook->hook_object($this->$name, "\$pines->{$name}->");
				return $this->$name;
			} catch (Exception $e) {
				return;
			}
		}
		if (in_array($name, array('configurator', 'log_manager', 'entity_manager', 'db_manager', 'user_manager', 'ability_manager', 'editor')) && isset($this->standard_classes[$name])) {
			global $pines;
			$this->$name = new $this->standard_classes[$name];
			$pines->hook->hook_object($this->$name, "\$pines->{$name}->");
			return $this->$name;
		}
	}

	/**
	 * Checks whether a variable is set.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 *
	 * This functions checks whether a class can be loaded for class variables.
	 *
	 * @param string $name The name of the variable.
	 * @return bool
	 */
	public function __isset($name) {
		global $pines;
		if (preg_match('/^run_/', $name)) {
			$new_name = preg_replace('/^run_/', 'com_', $name);
			if (class_exists($new_name))
				return true;
			if (is_array($pines->class_files) && isset($pines->class_files[$new_name]))
				return true;
			return false;
		}
		if (in_array($name, array('configurator', 'log_manager', 'entity_manager', 'db_manager', 'user_manager', 'ability_manager', 'editor')) && isset($this->standard_classes[$name])) {
			return isset($this->standard_classes[$name]);
		}
		return false;
	}

	/**
	 * Sets a variable.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 * 
	 * This function catches any standard system classes, so they don't get set
	 * to the name of their class. This allows them to be dynamically loaded
	 * when they are first called.
	 *
	 * @param string $name The name of the variable.
	 * @param string $value The value of the variable.
	 * @return mixed The value of the variable.
	 */
	public function __set($name, $value) {
		if (in_array($name, array('configurator', 'log_manager', 'entity_manager', 'db_manager', 'user_manager', 'ability_manager', 'editor')) && is_string($value)) {
			return $this->standard_classes[$name] = $value;
		} else {
			return $this->$name = $value;
		}
	}
}

?>
<?php
/**
 * Dynamic hook_override class.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * An object used to replace another object, so it can be successfully hooked.
 * 
 * This class is dynamically edited during the takeover of an object for
 * hooking.
 * 
 * @package Pines
 * @todo Include the generated code for system classes so hook doesn't have to scan them every time the system is started.
 */
class hook_override__NAMEHERE_ extends hook_override {
	/**
	 * Used to store the overridden class.
	 * @var mixed $_p_object
	 */
	protected $_p_object = null;
	/**
	 * Used to store the prefix (the object's variable name).
	 * @var string $_p_prefix
	 */
	private $_p_prefix = '';

	function __construct(&$object, $prefix = '') {
		$this->_p_object = $object;
		$this->_p_prefix = $prefix;
	}

	function &__get($name) {
		return $val =& $this->_p_object->$name;
	}

	function __set($name, $value) {
		return $this->_p_object->$name = $value;
	}

	function __isset($name) {
		return isset($this->_p_object->$name);
	}

	function __unset($name) {
		unset($this->_p_object->$name);
	}

	function __toString() {
		return (string) $this->_p_object;
	}

	function __invoke() {
		if (method_exists($this->_p_object, '__invoke')) {
			$args = func_get_args();
			return call_user_func_array(array($this->_p_object, '__invoke'), $args);
		}
	}

	function __set_state() {
		if (method_exists($this->_p_object, '__set_state')) {
			$args = func_get_args();
			return call_user_func_array(array($this->_p_object, '__set_state'), $args);
		}
	}

	function __clone() {
		global $pines;
		// TODO: Test this. Make sure cloning works properly.
		$new_object = clone $this->_p_object;
		$pines->hook->hook_object($new_object, get_class($new_object).'->', false);
		return $new_object;
	}

//#CODEHERE#
}

?>
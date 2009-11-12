<?php
/**
 * Dynamic hook_override class.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
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
 */
class hook_override__NAMEHERE_ {
	/**
	 * Used to store the overridden class.
	 * @var mixed $_p_object
	 */
	private $_p_object = null;
	/**
	 * Used to store the prefix (the object's variable name).
	 * @var string $_p_prefix
	 */
	private $_p_prefix = '';

	function __construct(&$object, $prefix = '') {
		$this->_p_object = $object;
		$this->_p_prefix = $prefix;
	}

	/* This shouldn't be hookable, and is called when this class is destroyed.
	function __destruct() {
		if (method_exists($this->_p_object, '__destruct')) {
			$args = func_get_args();
			return call_user_func_array(array($this->_p_object, '__destruct'), $args);
		}
	}
	*/

	/* These will be created dynamically and are thus hookable.
	function &__call($name, $arguments) {
		if (method_exists($this->_p_object, '__call')) {
			$args = func_get_args();
			return call_user_func_array(array($this->_p_object, '__call'), $args);
		}
	}

	static function &__callStatic($name, $arguments) {
		if (method_exists($this->_p_object, '__callStatic')) {
			$args = func_get_args();
			return forward_static_call_array(array($this->_p_object, '__callStatic'), $args);
		}
	}*/

	function &__get($name) {
		if (method_exists($this->_p_object, '__get')) {
			$args = func_get_args();
			return call_user_func_array(array($this->_p_object, '__get'), $args);
		}
		return call_user_func_array(array($this->_p_object, '_p_get'), array($name, $_SESSION['secret']));
	}

	function __set($name, $value) {
		if (method_exists($this->_p_object, '__set')) {
			$args = func_get_args();
			return call_user_func_array(array($this->_p_object, '__set'), $args);
		}
		return call_user_func_array(array($this->_p_object, '_p_set'), array($name, $value, $_SESSION['secret']));
	}

	function __isset($name) {
		return isset($this->_p_object->$name);
	}

	function __unset($name) {
		if (method_exists($this->_p_object, '__unset')) {
			$args = func_get_args();
			return call_user_func_array(array($this->_p_object, '__unset'), $args);
		}
		return call_user_func_array(array($this->_p_object, '_p_unset'), array($name, $_SESSION['secret']));
	}

	function __toString() {
		return "{$this->_p_object}";
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
		if (method_exists($this->_p_object, '__clone')) {
			$args = func_get_args();
			return call_user_func_array(array($this->_p_object, '__clone'), $args);
		} else {
			return clone $this->_p_object;
		}
	}

//#CODEHERE#
}

?>
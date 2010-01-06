<?php
/**
 * hook class
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * An object oriented function/method hooking system.
 *
 * Hooks are used to call specific functions when another function is called.
 *
 * @package Pines
 */
class hook {
	/**
	 * An array of the available hooks.
	 * @var array $hooks
	 */
	protected $hooks = array();
	/**
	 * An array of the callbacks.
	 * @var array $callbacks
	 */
	protected $callbacks = array();

	/**
	 * Add a callback.
	 *
	 * A callback is called either before a function runs or after. If the
	 * callback runs before the function and returns false (or causes an error),
	 * the function will not be run. The callback is passed an array of
	 * arguments and is expected to return an array of arguments. Callbacks
	 * before a function are passed the arguments given when the function was
	 * called, while callbacks after a function are given the return value of
	 * that function. If the callback neglects to return anything, the function
	 * being called will not receive/return anything. Even if your callback does
	 * nothing with the arguments, it should still return them.
	 *
	 * If the hook is called explicitly, callbacks defined to run before the
	 * hook will run immediately followed by callbacks defined to run after.
	 *
	 * You can think of the $order as a timeline of functions to call, zero (0)
	 * being the actual function being hooked.
	 *
	 * Additional identical callbacks can be added in order to have a callback
	 * called multiple times for one hook.
	 *
	 * Note: Be careful to avoid recursive callbacks, as they may result in an
	 * infinite loop. All functions under $config are automatically defined as
	 * hooks.
	 *
	 * @param string $hook The name of the hook to catch.
	 * @param int $order The order can be negative, which will run before the function, or positive, which will run after the function. It cannot be zero.
	 * @param callback The callback.
	 * @return int The ID of the new callback.
	 */
	function add_callback($hook, $order, $function) {
		$callback = array($hook, $order, $function);
		$this->callbacks[] = $callback;
		return array_keys($this->callbacks, $callback);
	}

	/**
	 * Add a hook.
	 *
	 * A hook is the name of whatever function it should hook onto. You can also
	 * give a hook an arbitrary name, but be wary that it may already exist and
	 * it may result in your callback being falsely called. In order to reduce
	 * the chance of this, always use a plus sign (+) in front of arbitrary hook
	 * names.
	 *
	 * @param string $name The name of the hook.
	 * @return int The ID of the new hook, or the ID of a hook which already exists and has the same name.
	 */
	function add_hook($name) {
		if (array_search($name, $this->hooks) === false)
			$this->hooks[] = $name;
		return array_keys($this->hooks, $name);
	}

	/**
	 * Delete a callback by its ID.
	 *
	 * @param int $id The ID of the callback.
	 * @return int 1 if the callback was deleted, 2 if it didn't exist.
	 */
	function del_callback_by_id($id) {
		if (!isset($this->callbacks[$id])) return 2;
		unset($this->callbacks[$id]);
		return 1;
	}

	/**
	 * Delete a hook.
	 *
	 * Note: This does not delete callbacks associated with the hook, however,
	 * since the hook is being deleted, they should never be called again
	 * anyway.
	 *
	 * @param string $name The name of the hook.
	 * @return int 1 if the hook was deleted, 2 if it didn't exist.
	 */
	function del_hook($name) {
		$id = array_search($name, $this->hooks);
		if ($id === false) return 2;
		unset($this->hooks[$id]);
		return 1;
	}

	/**
	 * Delete a hook by its ID.
	 *
	 * Note: This does not delete callbacks associated with the hook, however,
	 * since the hook is being deleted, they should never be called again
	 * anyway.
	 *
	 * @param int $id The ID of the hook.
	 * @return int 1 if the hook was deleted, 2 if it didn't exist.
	 */
	function del_hook_by_id($id) {
		if (!isset($this->hooks[$id])) return 2;
		unset($this->hooks[$id]);
		return 1;
	}

	/**
	 * Get the array of callbacks.
	 *
	 * Callbacks are stored in arrays inside this array. Each array contains the
	 * values $hook, $order, $function, in that order.
	 *
	 * @return array An array of callbacks.
	 */
	function get_callbacks() {
		return $this->callbacks;
	}

	/**
	 * Get the array of hooks.
	 *
	 * @return array An array of hooks.
	 */
	function get_hooks() {
		return $this->hooks;
	}

	/**
	 * Hook an object.
	 *
	 * This hooks all functions defined in the given object.
	 *
	 * @param object &$object The object to hook.
	 * @param string $prefix The prefix used to call the object's methods. Usually something like "$object->".
	 * @param bool $recursive Whether to hook objects recursively.
	 * @return bool True on success, false on failure.
	 */
	function hook_object(&$object, $prefix = '', $recursive = true) {
		if (!is_object($object)) return false;
		// Make sure we don't take over the hook object, or we'll end up
		// recursively calling ourself.
		if (get_class($object) == 'hook') return false;

		$ref_class = new ReflectionObject($object);
		$methods = $ref_class->getMethods();
		foreach ($methods as $cur_ref_method) {
			$this->add_hook($prefix.$cur_ref_method->getName());
		}
		
		if ($recursive) {
			foreach ($object as $cur_name => &$cur_property) {
				if (is_object($cur_property))
					$this->hook_object($cur_property, $prefix.$cur_name.'->');
			}
		}
		
		$class_name = get_class($object);
		if (!class_exists("hook_override_$class_name")) {
			$reflection = new ReflectionObject($object);
			$methods = $reflection->getMethods();
			$code = '';
			foreach ($methods as $cur_method) {
				$fname = $cur_method->getName();
				if (in_array($fname, array('_p_get', '_p_set', '_p_unset', '__construct', '__destruct', '__get', '__set', '__isset', '__unset', '__toString', '__invoke', '__set_state', '__clone'))) continue;
				$fprefix = $cur_method->isStatic() ? 'static ' : '';
				$params = $cur_method->getParameters();
				$param_array = array();
				$param_call_array = array();
				foreach ($params as $cur_param) {
					$param_name = $cur_param->getName();
					$param_prefix = $cur_param->isPassedByReference() ? '&' : '';
					if ($cur_param->isDefaultValueAvailable()) {
						$param_suffix = ' = '.var_export($cur_param->getDefaultValue(), true);
					}
					$param_array[] = $param_prefix.'$'.$param_name.$param_suffix;
					$param_call_array[] = '$'.$param_name;
				}
				$code .= $fprefix."function $fname(".implode(', ', $param_array).") {\n";
				$code .= "\tglobal \$config;\n";
				$code .= "\t\$arguments = debug_backtrace(false);\n";
				$code .= "\t\$arguments = \$arguments[0]['args'];\n";
				$code .= "\t\$arguments = \$config->hook->run_callbacks(\$this->_p_prefix.'$fname', \$arguments, 'before', \$this->_p_object);\n";
				$code .= "\tif (\$arguments !== false) {\n";
				$code .= "\t\t\$return = call_user_func_array(array(\$this->_p_object, '$fname'), \$arguments);\n";
				$code .= "\t\t\$return = \$config->hook->run_callbacks(\$this->_p_prefix.'$fname', array(\$return), 'after', \$this->_p_object);\n";
				$code .= "\t\tif (is_array(\$return))\n";
				$code .= "\t\t\treturn \$return[0];\n";
				$code .= "\t}\n";
				$code .= "}\n\n";
			}
			$include = str_replace(array('_NAMEHERE_', '//#CODEHERE#', '<?php', '?>'), array($class_name, $code, '', ''), file_get_contents('system/classes/hook_override.php'));
			eval ($include);
		}

		eval ('$object = new hook_override_'.$class_name.' ($object, $prefix);');
		return true;
	}

	/**
	 * Run the callbacks for a given hook.
	 *
	 * Each callback is run and passed the array of arguments, and the name of
	 * the given hook. If any callback returns FALSE, the following callbacks
	 * will not be called, and FALSE will be returned.
	 *
	 * @param string $name The name of the hook.
	 * @param array $arguments An array of arguments to be passed to the callbacks.
	 * @param string $type The type of callbacks to run. 'before', 'after', or 'all'.
	 * @param mixed $object The object on which the hook was called.
	 * @return array|bool The array of arguments returned by the last callback or FALSE if a callback returned it.
	 */
	function run_callbacks($name, $arguments = array(), $type = 'all', $object = null) {
		foreach ($this->callbacks as $cur_callback) {
			if ($cur_callback[0] == $name || $cur_callback[0] == 'all') {
				if (($type == 'all' && $cur_callback[1] != 0) || ($type == 'before' && $cur_callback[1] < 0) || ($type == 'after' && $cur_callback[1] > 0)) {
					$arguments = call_user_func_array($cur_callback[2], array($arguments, $name, $object));
					if ($arguments === false) return false;
				}
			}
		}
		return $arguments;
	}
}

?>
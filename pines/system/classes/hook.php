<?php
/**
 * hook class
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * An object method hooking system.
 *
 * Hooks are used to call a callback when a method is called and optionally
 * manipulate the parameters/return values.
 *
 * @package Pines
 */
class hook extends p_base {
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

	private $hook_file;

	function __construct() {
		include_once('system/classes/hook_override.php');
		$this->hook_file = file_get_contents('system/classes/hook_override_extend.php');
	}

	/**
	 * Add a callback.
	 *
	 * A callback is called either before a method runs or after. If the
	 * callback runs before the method and returns false (or causes an error),
	 * the method will not be run. The callback is passed an array of arguments
	 * and is expected to return an array of arguments. Callbacks before a
	 * method are passed the arguments given when the method was called, while
	 * callbacks after a method are passed the return value of that method. If
	 * the callback neglects to return anything, the method being called will
	 * not receive/return anything. Even if your callback does nothing with the
	 * arguments, it should still return them.
	 *
	 * If the hook is called explicitly, callbacks defined to run before the
	 * hook will run immediately followed by callbacks defined to run after.
	 *
	 * You can think of the $order as a timeline of functions to call, zero (0)
	 * being the actual method being hooked.
	 *
	 * Additional identical callbacks can be added in order to have a callback
	 * called multiple times for one hook.
	 *
	 * The hook "all" is a psuedo hook which will run regardless of what hook
	 * was actually caught. Callbacks attached to the "all" hook will run before
	 * callbacks attached to the actual hook.
	 *
	 * Note: Be careful to avoid recursive callbacks, as they may result in an
	 * infinite loop. All methods under $pines are automatically defined as
	 * hooks.
	 *
	 * @param string $hook The name of the hook to catch.
	 * @param int $order The order can be negative, which will run before the method, or positive, which will run after the method. It cannot be zero.
	 * @param callback The callback.
	 * @return array An array containing the IDs of the new callback and all matching callbacks.
	 * @uses hook::sort_callbacks() To resort the callback array in the correct order.
	 */
	function add_callback($hook, $order, $function) {
		$callback = array($order, $function);
		if (!isset($this->callbacks[$hook]))
			$this->callbacks[$hook] == array();
		$this->callbacks[$hook][] = $callback;
		uasort($this->callbacks[$hook], array($this, 'sort_callbacks'));
		return array_keys($this->callbacks[$hook], $callback);
	}

	/**
	 * Add a hook.
	 *
	 * A hook is the name of whatever method it should hook onto. You can also
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
		$id = array_keys($this->hooks, $name);
		return $id[0];
	}

	/**
	 * Delete a callback by its ID.
	 *
	 * @param string $hook The name of the callback's hook.
	 * @param int $id The ID of the callback.
	 * @return int 1 if the callback was deleted, 2 if it didn't exist.
	 */
	function del_callback_by_id($hook, $id) {
		if (!isset($this->callbacks[$hook][$id])) return 2;
		unset($this->callbacks[$hook][$id]);
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
	 * Callbacks are stored in arrays inside this array. The keys of this array
	 * are the name of the hook whose callbacks are contained in its value as an
	 * array. Each array contains the values $order, $function, in that order.
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
	 * This hooks all (public) methods defined in the given object.
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
		$class_name = get_class($object);
		if ($class_name == 'hook') return false;

		if ($recursive) {
			foreach ($object as $cur_name => &$cur_property) {
				if (is_object($cur_property))
					$this->hook_object($cur_property, $prefix.$cur_name.'->');
			}
		}

		if (!class_exists("hook_override_$class_name")) {

			$reflection = new ReflectionObject($object);
			$methods = $reflection->getMethods();

			$code = '';
			foreach ($methods as &$cur_method) {
				if (!$cur_method->isPublic())
					continue;
				$fname = $cur_method->getName();
				if (in_array($fname, array('__construct', '__destruct', '__get', '__set', '__isset', '__unset', '__toString', '__invoke', '__set_state', '__clone', '__sleep')))
					continue;
				// Create a hook for this method.
				$this->add_hook($prefix.$fname);

				//$fprefix = $cur_method->isFinal() ? 'final ' : '';
				$fprefix = $cur_method->isStatic() ? 'static ' : '';
				$params = $cur_method->getParameters();
				$param_array = array();
				$param_call_array = array();
				foreach ($params as $cur_param) {
					$param_name = $cur_param->getName();
					$param_prefix = $cur_param->isPassedByReference() ? '&' : '';
					if ($cur_param->isDefaultValueAvailable()) {
						$param_suffix = ' = '.var_export($cur_param->getDefaultValue(), true);
					} else {
						$param_suffix = '';
					}
					$param_array[] = $param_prefix.'$'.$param_name.$param_suffix;
					$param_call_array[] = '$'.$param_name;
				}
				$code .= $fprefix."function $fname(".implode(', ', $param_array).") {\n";
				$code .= "\tglobal \$pines;\n";
				$code .= "\t\$arguments = debug_backtrace(false);\n";
				$code .= "\t\$arguments = \$arguments[0]['args'];\n";
				$code .= "\t\$function = array(\$this->_p_object, '$fname');\n";
				$code .= "\t\$arguments = \$pines->hook->run_callbacks(\$this->_p_prefix.'$fname', \$arguments, 'before', \$this->_p_object, \$function);\n";
				$code .= "\tif (\$arguments !== false) {\n";
				$code .= "\t\t\$return = call_user_func_array(\$function, \$arguments);\n";
				$code .= "\t\t\$return = \$pines->hook->run_callbacks(\$this->_p_prefix.'$fname', array(\$return), 'after', \$this->_p_object, \$function);\n";
				$code .= "\t\tif (is_array(\$return))\n";
				$code .= "\t\t\treturn \$return[0];\n";
				$code .= "\t}\n";
				$code .= "}\n\n";
			}
			// Build a hook_override class.
			$include = str_replace(array('_NAMEHERE_', '//#CODEHERE#', '<?php', '?>'), array($class_name, $code, '', ''), $this->hook_file);
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
	 * @param mixed &$object The object on which the hook was called.
	 * @param callback &$function The function which is called at "0". You can change this in the "before" callbacks to effectively takeover a function.
	 * @return array|bool The array of arguments returned by the last callback or FALSE if a callback returned it.
	 */
	function run_callbacks($name, $arguments = array(), $type = 'all', &$object = null, &$function = null) {
		if (!in_array($name, $this->hooks))
			return $arguments;
		if (isset($this->callbacks['all'])) {
			foreach ($this->callbacks['all'] as $cur_callback) {
				if (($type == 'all' && $cur_callback[0] != 0) || ($type == 'before' && $cur_callback[0] < 0) || ($type == 'after' && $cur_callback[0] > 0)) {
					$arguments = call_user_func_array($cur_callback[1], array($arguments, $name, &$object, &$function));
					if ($arguments === false) return false;
				}
			}
		}
		if (isset($this->callbacks[$name])) {
			foreach ($this->callbacks[$name] as $cur_callback) {
				if (($type == 'all' && $cur_callback[0] != 0) || ($type == 'before' && $cur_callback[0] < 0) || ($type == 'after' && $cur_callback[0] > 0)) {
					$arguments = call_user_func_array($cur_callback[1], array($arguments, $name, &$object, &$function));
					if ($arguments === false) return false;
				}
			}
		}
		return $arguments;
	}

	/**
	 * Sort function for callback sorting.
	 *
	 * This assures that callbacks are executed in the correct order. Callback
	 * IDs are preserved as long as uasort() is used.
	 *
	 * @param array $a The first callback in the comparison.
	 * @param arary $b The second callback in the comparison.
	 * @return int 0 for equal, -1 for less than, 1 for greater than.
	 * @access private
	 */
	private function sort_callbacks($a, $b) {
		if ($a[0] == $b[0])
			return 0;
		return ($a[0] < $b[0]) ? -1 : 1;
	}
}

/*

	function hook_object(&$object, $prefix = '', $recursive = true) {
		if (!is_object($object)) return false;
		// Make sure we don't take over the hook object, or we'll end up
		// recursively calling ourself.
		$class_name = get_class($object);
		if ($class_name == 'hook') return false;

		if ($recursive) {
			foreach ($object as $cur_name => &$cur_property) {
				if (is_object($cur_property))
					$this->hook_object($cur_property, $prefix.$cur_name.'->');
			}
		}

		if (!class_exists("hook_override_$class_name")) {

			$reflection = new ReflectionObject($object);
			$methods = $reflection->getMethods();

			$code = '';
			foreach ($methods as $cur_method) {
				if (!$cur_method->isPublic())
					continue;
				$fname = $cur_method->getName();
				if (in_array($fname, array('__construct', '__destruct', '__get', '__set', '__isset', '__unset', '__toString', '__invoke', '__set_state', '__clone', '__sleep')))
					continue;
				// Create a hook for this method.
				$this->add_hook($prefix.$fname);

				//$fprefix = $cur_method->isFinal() ? 'final ' : '';
				$fprefix = $cur_method->isStatic() ? 'static ' : '';
				$params = $cur_method->getParameters();
				$param_array = array();
				$param_call_array = array();
				foreach ($params as $cur_param) {
					$param_name = $cur_param->getName();
					$param_prefix = $cur_param->isPassedByReference() ? '&' : '';
					if ($cur_param->isDefaultValueAvailable()) {
						$param_suffix = ' = '.var_export($cur_param->getDefaultValue(), true);
					} else {
						$param_suffix = '';
					}
					$param_array[] = $param_prefix.'$'.$param_name.$param_suffix;
					$param_call_array[] = '$'.$param_name;
				}
				$code .= $fprefix."function $fname(".implode(', ', $param_array).") {\n";
				$code .= "\tglobal \$pines;\n";
				$code .= "\t\$arguments = debug_backtrace(false);\n";
				$code .= "\t\$arguments = \$arguments[0]['args'];\n";
				$code .= "\t\$arguments = \$pines->hook->run_callbacks(\$this->_p_prefix.'$fname', \$arguments, 'before', \$this->_p_object);\n";
				$code .= "\tif (\$arguments !== false) {\n";
				$code .= "\t\t\$return = call_user_func_array(array(\$this->_p_object, '$fname'), \$arguments);\n";
				$code .= "\t\t\$return = \$pines->hook->run_callbacks(\$this->_p_prefix.'$fname', array(\$return), 'after', \$this->_p_object);\n";
				$code .= "\t\tif (is_array(\$return))\n";
				$code .= "\t\t\treturn \$return[0];\n";
				$code .= "\t}\n";
				$code .= "}\n\n";
			}
			// Build a hook_override class.
			$include = str_replace(array('_NAMEHERE_', '//#CODEHERE#', '<?php', '?>'), array($class_name, $code, '', ''), $this->hook_file);
			eval ($include);
		}

		eval ('$object = new hook_override_'.$class_name.' ($object, $prefix);');
		return true;
	}
 */
?>
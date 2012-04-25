<?php
/**
 * hook class
 *
 * @package Core
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * An object method hooking system.
 *
 * Hooks are used to call a callback when a method is called and optionally
 * manipulate the arguments/function call/return value.
 *
 * @package Core
 */
class hook {
	/**
	 * An array of the callbacks for each hook.
	 * @var array
	 */
	protected $hooks = array();
	/**
	 * A copy of the hook_override_extend file.
	 * @var string
	 */
	private $hook_file;

	public function __construct() {
		include_once('system/classes/hook_override.php');
		$this->hook_file = file_get_contents('system/classes/hook_override_extend.php');
	}

	/**
	 * Add a callback.
	 *
	 * A callback is called either before a method runs or after. The callback
	 * is passed an array of arguments or return value which it can freely
	 * manipulate. If the callback runs before the method and sets the arguments
	 * array to false (or causes an error), the method will not be run.
	 * Callbacks before a method are passed the arguments given when the method
	 * was called, while callbacks after a method are passed the return value
	 * (in an array) of that method.
	 *
	 * The callback can receive up to 5 arguments, in this order:
	 *
	 * - &$arguments - An array of either arguments or a return value.
	 * - $name - The name of the hook.
	 * - &$object - The object on which the hook caught a method call.
	 * - &$function - A callback for the method call which was caught. Altering
	 *   this will cause a different function/method to run.
	 * - &$data - An array in which callbacks can store data to communicate with
	 *   later callbacks.
	 *
	 * A hook is the name of whatever method it should catch. A hook can also
	 * have an arbitrary name, but be wary that it may already exist and it may
	 * result in your callback being falsely called. In order to reduce the
	 * chance of this, always use a plus sign (+) and your component's name to
	 * begin arbitrary hook names. E.g. "+com_games_player_bonus".
	 *
	 * If the hook is called explicitly, callbacks defined to run before the
	 * hook will run immediately followed by callbacks defined to run after.
	 *
	 * A negative $order value means the callback will be run before the method,
	 * while a positive value means it will be run after. The smaller the order
	 * number, the sooner the callback will be run. You can think of the order
	 * value as a timeline of callbacks, zero (0) being the actual method being
	 * hooked.
	 *
	 * Additional identical callbacks can be added in order to have a callback
	 * called multiple times for one hook.
	 *
	 * The hook "all" is a pseudo hook which will run regardless of what was
	 * actually caught. Callbacks attached to the "all" hook will run before
	 * callbacks attached to the actual hook.
	 *
	 * Note: Be careful to avoid recursive callbacks, as they may result in an
	 * infinite loop. All methods under $pines are automatically hooked.
	 *
	 * @param string $hook The name of the hook to catch.
	 * @param int $order The order can be negative, which will run before the method, or positive, which will run after the method. It cannot be zero.
	 * @param callback The callback.
	 * @return array An array containing the IDs of the new callback and all matching callbacks.
	 * @uses hook::sort_callbacks() To resort the callback array in the correct order.
	 */
	public function add_callback($hook, $order, $function) {
		$callback = array($order, $function);
		if (!isset($this->hooks[$hook]))
			$this->hooks[$hook] == array();
		$this->hooks[$hook][] = $callback;
		uasort($this->hooks[$hook], array($this, 'sort_callbacks'));
		return array_keys($this->hooks[$hook], $callback);
	}

	/**
	 * Delete a callback by its ID.
	 *
	 * @param string $hook The name of the callback's hook.
	 * @param int $id The ID of the callback.
	 * @return int 1 if the callback was deleted, 2 if it didn't exist.
	 */
	public function del_callback_by_id($hook, $id) {
		if (!isset($this->hooks[$hook][$id])) return 2;
		unset($this->hooks[$hook][$id]);
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
	public function get_callbacks() {
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
	public function hook_object(&$object, $prefix = '', $recursive = true) {
		if ((object) $object === $object)
			$is_string = false;
		else
			$is_string = true;

		// Make sure we don't take over the hook object, or we'll end up
		// recursively calling ourself. Some system classes shouldn't be hooked.
		$class_name = $is_string ? $object : get_class($object);
		if (in_array($class_name, array('hook', 'depend', 'config', 'info')))
			return false;

		if ($recursive && !$is_string) {
			foreach ($object as $cur_name => &$cur_property) {
				if ((object) $cur_property === $cur_property)
					$this->hook_object($cur_property, $prefix.$cur_name.'->');
			}
		}

		if (!class_exists("hook_override_$class_name")) {
			// This can make it faster, but might introduce security problems.
			//pines_session();
			//if (isset($_SESSION['hook_cache']["hook_override_$class_name"])) {
			//	eval($_SESSION['hook_cache']["hook_override_$class_name"]);
			//} else {
			if ($is_string)
				$reflection = new ReflectionClass($object);
			else
				$reflection = new ReflectionObject($object);
			$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

			$code = '';
			foreach ($methods as &$cur_method) {
				$fname = $cur_method->getName();
				if (in_array($fname, array('__construct', '__destruct', '__get', '__set', '__isset', '__unset', '__toString', '__invoke', '__set_state', '__clone', '__sleep')))
					continue;

				//$fprefix = $cur_method->isFinal() ? 'final ' : '';
				$fprefix = $cur_method->isStatic() ? 'static ' : '';
				$params = $cur_method->getParameters();
				$param_array = array(); //$param_name_array
				foreach ($params as &$cur_param) {
					$param_name = $cur_param->getName();
					$param_prefix = $cur_param->isPassedByReference() ? '&' : '';
					if ($cur_param->isDefaultValueAvailable())
						$param_suffix = ' = '.var_export($cur_param->getDefaultValue(), true);
					else
						$param_suffix = '';
					$param_array[] = "{$param_prefix}\${$param_name}{$param_suffix}";
					//$param_name_array[] = "{$param_prefix}\${$param_name}";
				}
				unset($cur_param);
				$code .= $fprefix."function $fname(".implode(', ', $param_array).") {\n"
				."\tglobal \$pines;\n"
				// We must use a debug_backtrace, because that's the best way to
				// get all the passed arguments, by reference. 5.4 and up lets
				// us limit it to 1 frame.
				.(version_compare(PHP_VERSION, '5.4.0') >= 0 ?
					"\t\$arguments = debug_backtrace(false, 1);\n" :
					"\t\$arguments = debug_backtrace(false);\n"
				)
				."\t\$arguments = \$arguments[0]['args'];\n"
				// This method works, but isn't faster, and might introduce bugs.
				//."\t\$arguments = array(".implode(', ', $param_name_array).");\n"
				//."\t\$real_arg_count = func_num_args();\n"
				//."\t\$arg_count = count(\$arguments);\n"
				//."\tif (\$real_arg_count > \$arg_count) {\n"
				//."\t\tfor (\$i = \$arg_count; \$i < \$real_arg_count; \$i++)\n"
				//."\t\t\t\$arguments[] = func_get_arg(\$i);\n"
				//."\t}\n"
				."\t\$function = array(\$this->_p_object, '$fname');\n"
				."\t\$data = array();\n"
				."\t\$pines->hook->run_callbacks(\$this->_p_prefix.'$fname', \$arguments, 'before', \$this->_p_object, \$function, \$data);\n"
				."\tif (\$arguments !== false) {\n"
				."\t\t\$return = array(call_user_func_array(\$function, \$arguments));\n"
				."\t\t\$pines->hook->run_callbacks(\$this->_p_prefix.'$fname', \$return, 'after', \$this->_p_object, \$function, \$data);\n"
				."\t\tif ((array) \$return === \$return)\n"
				."\t\t\treturn \$return[0];\n"
				."\t}\n"
				."}\n\n";
			}
			unset($cur_method);
			// Build a hook_override class.
			$include = str_replace(array('_NAMEHERE_', '//#CODEHERE#', '<?php', '?>'), array($class_name, $code, '', ''), $this->hook_file);
			eval ($include);
			//	if (!$_SESSION['hook_cache'])
			//		$_SESSION['hook_cache'] = array();
			//	$_SESSION['hook_cache']["hook_override_$class_name"] = $include;
			//}
		}

		eval ('$object = new hook_override_'.$class_name.' ($object, $prefix);');
		return true;
	}

	/**
	 * Run the callbacks for a given hook.
	 *
	 * Each callback is run and passed the array of arguments, and the name of
	 * the given hook. If any callback changes $arguments to FALSE, the
	 * following callbacks will not be called, and FALSE will be returned.
	 *
	 * @param string $name The name of the hook.
	 * @param array &$arguments An array of arguments to be passed to the callbacks.
	 * @param string $type The type of callbacks to run. 'before', 'after', or 'all'.
	 * @param mixed &$object The object on which the hook was called.
	 * @param callback &$function The function which is called at "0". You can change this in the "before" callbacks to effectively takeover a function.
	 * @param array &$data A data array for callback communication.
	 */
	public function run_callbacks($name, &$arguments = array(), $type = 'all', &$object = null, &$function = null, &$data = array()) {
		if (isset($this->hooks['all'])) {
			foreach ($this->hooks['all'] as $cur_callback) {
				if (($type == 'all' && $cur_callback[0] != 0) || ($type == 'before' && $cur_callback[0] < 0) || ($type == 'after' && $cur_callback[0] > 0)) {
					call_user_func_array($cur_callback[1], array(&$arguments, $name, &$object, &$function, &$data));
					if ($arguments === false) return;
				}
			}
		}
		if (isset($this->hooks[$name])) {
			foreach ($this->hooks[$name] as $cur_callback) {
				if (($type == 'all' && $cur_callback[0] != 0) || ($type == 'before' && $cur_callback[0] < 0) || ($type == 'after' && $cur_callback[0] > 0)) {
					call_user_func_array($cur_callback[1], array(&$arguments, $name, &$object, &$function, &$data));
					if ($arguments === false) return;
				}
			}
		}
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
?>
<?php
/**
 * An object oriented function/method hooking system.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A class to provide hooks.
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
     * the function will not be run.
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
     * Run the callbacks for a given hook.
     *
     * Note: If the hook refers to an actual function/method, it will not be
     * run. If that is what you want, run the actual function/method.
     *
     * @todo Code this.
     */
    function run_hook($name) {
        // Not done.
    }

    /**
     * Scan an object and assign a hook for each of its methods.
     *
     * @param object &$object The object to scan.
     * @param bool $recursive Whether to scan objects recursively.
     * @return bool True on success, false on failure.
     */
    function scan_object(&$object, $prefix = '', $recursive = true) {
        if (!is_object($object)) return false;
        $ref_class = new ReflectionObject($object);
        $methods = $ref_class->getMethods();
        foreach ($methods as $cur_ref_method) {
            $this->add_hook($prefix.$cur_ref_method->getName());
        }
        if ($recursive) {
            $properties = $ref_class->getProperties();
            foreach ($properties as $cur_ref_property) {
                if ($cur_ref_property->isPublic()) {
                    $value = $cur_ref_property->getValue($object);
                    if (is_object($value))
                        $this->scan_object($value, $prefix.$cur_ref_property->getName().'->');
                }
            }
        }
        return true;
    }

    /**
     * Retrieve the name of a variable.
     *
     * Credit: http://us3.php.net/manual/en/language.variables.php#76245
     *
     * @param mixed &$var The variable.
     * @param mixed $scope The scope of the variable.
     */
    function var_name(&$var, $scope = 0) {
        $old = $var;
        if (($key = array_search($var = 'unique'.rand().'value', !$scope ? $GLOBALS : $scope)) && $var = $old) return $key;
    }
}

?>
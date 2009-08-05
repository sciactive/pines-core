<?php
/**
 * Dynamic hook object.
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
 */
class hook_override {
    /**
     * Used to store the overridden class.
     * @var mixed $new_object
     */
    private $new_object = null;
    /**
     * Used to store the prefix (the object's variable name).
     * @var string $prefix
     */
    private $prefix = '';

    function __construct($object, $prefix = '') {
        $this->new_object = $object;
        $this->prefix = $prefix;
    }

    function __destruct() {
        if (method_exists($this->new_object, '__destruct'))
            return call_user_func_array(array($this->new_object, '__destruct'), func_get_args());
    }

    function __call($name, $arguments) {
        global $config;
        // Make sure we don't take over the hook object, or we'll end up
        // recursively calling ourself.
        if (get_class($this->new_object) == 'hook')
            return call_user_func_array(array($this->new_object, $name), $arguments);
        $arguments = $config->hook->run_callbacks($this->prefix.$name, $arguments, 'before');
        if ($arguments !== false) {
            $return[0] = call_user_func_array(array($this->new_object, $name), $arguments);
            $return = $config->hook->run_callbacks($this->prefix.$name, $return, 'after');
            if (is_array($return))
                return $return[0];
        }
    }

    function __callStatic($name, $arguments) {
        global $config;
        // Make sure we don't take over the hook object, or we'll end up
        // recursively calling ourself.
        if (get_class($this->new_object) == 'hook')
            return call_user_func_array(array($this->new_object, $name), $arguments);
        $arguments = $config->hook->run_callbacks($this->prefix.$name, $arguments, 'before');
        if ($arguments !== false) {
            $return[0] = forward_static_call_array(array($this->new_object, $name), $arguments);
            $return = $config->hook->run_callbacks($this->prefix.$name, $return, 'after');
            if (is_array($return))
                return $return[0];
        }
    }

    function __get($name) {
        if (method_exists($this->new_object, '__get'))
            return call_user_func_array(array($this->new_object, '__get'), func_get_args());
        return call_user_func_array(array($this->new_object, '_p_get'), array($name, $_SESSION['secret']));
    }

    function __set($name, $value) {
        if (method_exists($this->new_object, '__set'))
            return call_user_func_array(array($this->new_object, '__set'), func_get_args());
        return call_user_func_array(array($this->new_object, '_p_set'), array($name, $value, $_SESSION['secret']));
    }

    function __isset($name) {
        return isset($this->new_object->$name);
    }

    function __unset($name) {
        if (method_exists($this->new_object, '__unset'))
            return call_user_func_array(array($this->new_object, '__unset'), func_get_args());
        return call_user_func_array(array($this->new_object, '_p_unset'), array($name, $_SESSION['secret']));
    }

    function __toString() {
        return "{$this->new_object}";
    }

    function __invoke() {
        if (method_exists($this->new_object, '__invoke'))
            return call_user_func_array(array($this->new_object, '__invoke'), func_get_args());
    }

    function __set_state() {
        if (method_exists($this->new_object, '__set_state'))
            return call_user_func_array(array($this->new_object, '__set_state'), func_get_args());
    }

    function __clone() {
        if (method_exists($this->new_object, '__clone')) {
            return call_user_func_array(array($this->new_object, '__clone'), func_get_args());
        } else {
            return clone $this->new_object;
        }
    }
}

?>
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
     * @var mixed $_p_new_object
     */
    private $_p_new_object = null;
    /**
     * Used to store the prefix (the object's variable name).
     * @var string $_p_prefix
     */
    private $_p_prefix = '';

    private $test = '';

    function __construct(&$object, $prefix = '') {
        $this->_p_new_object = $object;
        $this->_p_prefix = $prefix;
        /*$reflection = new ReflectionObject($object);
        $this->test = eval($reflection->export($object, true));
        var_dump($this->test);
        $methods = $reflection->getMethods();
        foreach ($methods as $cur_method) {
            //$name = $cur_method->getName();
            //$this->$name = create_function($cur_method->getParameters, '');

            //$cur_method->export($object, $cur_method->getName());

            //echo $cur_method;
        }*/
    }

    function __destruct() {
        if (method_exists($this->_p_new_object, '__destruct')) {
            $args = func_get_args();
            return call_user_func_array(array($this->_p_new_object, '__destruct'), $args);
        }
    }

    // not passing things by reference!!
    function &__call($name, $arguments) {
        global $config;
        $debug = debug_backtrace(false);
        $arguments =& $debug[1]['args'];
        //var_dump($arguments);
        // Make sure we don't take over the hook object, or we'll end up
        // recursively calling ourself.
        if (get_class($this->_p_new_object) == 'hook')
            return call_user_func_array(array($this->_p_new_object, $name), $arguments);
        $arguments = $config->hook->run_callbacks($this->_p_prefix.$name, $arguments, 'before');
        if ($arguments !== false) {
            $return = call_user_func_array(array($this->_p_new_object, $name), $arguments);
            $return = $config->hook->run_callbacks($this->_p_prefix.$name, array($return), 'after');
            if (is_array($return))
                return $return[0];
        }
    }

    function &__callStatic($name, $arguments) {
        global $config;
        // Make sure we don't take over the hook object, or we'll end up
        // recursively calling ourself.
        if (get_class($this->_p_new_object) == 'hook')
            return call_user_func_array(array($this->_p_new_object, $name), $arguments);
        $arguments = $config->hook->run_callbacks($this->_p_prefix.$name, $arguments, 'before');
        if ($arguments !== false) {
            $return[0] = forward_static_call_array(array(&$this->_p_new_object, $name), $arguments);
            $return = $config->hook->run_callbacks($this->_p_prefix.$name, $return, 'after');
            if (is_array($return))
                return $return[0];
        }
    }

    function &__get($name) {
        if (method_exists($this->_p_new_object, '__get')) {
            $args = func_get_args();
            return call_user_func_array(array($this->_p_new_object, '__get'), $args);
        }
        return call_user_func_array(array($this->_p_new_object, '_p_get'), array($name, $_SESSION['secret']));
    }

    function __set($name, $value) {
        if (method_exists($this->_p_new_object, '__set')) {
            $args = func_get_args();
            return call_user_func_array(array($this->_p_new_object, '__set'), $args);
        }
        return call_user_func_array(array($this->_p_new_object, '_p_set'), array($name, $value, $_SESSION['secret']));
    }

    function __isset($name) {
        return isset($this->_p_new_object->$name);
    }

    function __unset($name) {
        if (method_exists($this->_p_new_object, '__unset')) {
            $args = func_get_args();
            return call_user_func_array(array($this->_p_new_object, '__unset'), $args);
        }
        return call_user_func_array(array($this->_p_new_object, '_p_unset'), array($name, $_SESSION['secret']));
    }

    function __toString() {
        return "{$this->_p_new_object}";
    }

    function __invoke() {
        if (method_exists($this->_p_new_object, '__invoke')) {
            $args = func_get_args();
            return call_user_func_array(array($this->_p_new_object, '__invoke'), $args);
        }
    }

    function __set_state() {
        if (method_exists($this->_p_new_object, '__set_state')) {
            $args = func_get_args();
            return call_user_func_array(array($this->_p_new_object, '__set_state'), $args);
        }
    }

    function __clone() {
        if (method_exists($this->_p_new_object, '__clone')) {
            $args = func_get_args();
            return call_user_func_array(array($this->_p_new_object, '__clone'), $args);
        } else {
            return clone $this->_p_new_object;
        }
    }
}

?>
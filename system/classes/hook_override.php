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
    private $new_object = null;

    function __construct($object) {
        $this->new_object = $object;
        $this->new_object->___get_private = create_function('$name, $secret', 'if ( isset($this->$name) && ($secret == $_SESSION[\'secret\']) ) return $this->$name;');
        $this->new_object->___set_private = create_function('$name, $value, $secret', 'if ( $secret == $_SESSION[\'secret\'] ) return ($this->$name = $value);');
        //var_dump($this->new_object);
    }

    function __destruct() {
        if (method_exists($this->new_object, '__destruct'))
            return call_user_func_array(array($this->new_object, '__destruct'), func_get_args());
    }

    function __call($name, $arguments) {
        return call_user_func_array(array($this->new_object, $name), $arguments);
    }

    function __callStatic($name, $arguments) {
        return forward_static_call_array(array($this->new_object, $name), $arguments);
    }

    function __get($name) {
        if (method_exists($this->new_object, '__get'))
            return call_user_func_array(array($this->new_object, '__get'), func_get_args());
        //return call_user_func_array(array($this->new_object, '___get_private'), array($name, $_SESSION['secret']));
        try {
            $prop = new ReflectionProperty(get_class($this->new_object), $name);
            // TODO: Find a way to access private properties without requiring 5.3.0.
            $prop->setAccessible(true); // Requires >= PHP 5.3.0
            return $prop->getValue($this->new_object);
        } catch (ReflectionException $e) {
            return $this->new_object->$name;
        }
        //return $this->new_object->{___get_private} ($name, $_SESSION['secret']);
    }

    function __set($name, $value) {
        //return $this->new_object->___set_private($name, $_SESSION['secret']);
        return ($this->new_object->$name = $value);
    }

    function __isset($name) {
        return isset($this->new_object->$name);
    }

    function __unset($name) {
        unset($this->new_object->$name);
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
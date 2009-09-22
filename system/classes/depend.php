<?php
/**
 * Depend Class.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Dependency checker.
 *
 * @package Pines
 */
class depend extends p_base {
    public $checkers = array();
    
    function __construct() {
        global $config;
        $this->checkers['ability'] = array($config->depend, 'check_ability');
        $this->checkers['option'] = array($config->depend, 'check_ability');
        $this->checkers['ability'] = array($config->depend, 'check_ability');
    }
    
    function check($type, $value) {
        call_user_func($this->checkers[$type], $value);
    }

    function check_ability($value) {
        if (preg_match('/[!&|()]/', $value))
            return $this->simple_parse($value, array($config->depend, 'check_ability'));
        return gatekeeper($value);
    }

    function check_option($value) {

    }

    function check_action($value) {

    }

    function simple_parse($value, $callback) {
        // ex: !val1&(val2|!val3|(val2&val4))
    }
}

?>
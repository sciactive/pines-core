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
    /**
     * An array of dependency checker callbacks.
     *
     * @var array $checkers
     */
    public $checkers = array();
    
    function __construct() {
        global $config;
        $this->checkers['ability'] = array($this, 'check_ability');
        $this->checkers['option'] = array($this, 'check_option');
        $this->checkers['action'] = array($this, 'check_action');
    }
    
    function check($type, $value) {
        return call_user_func($this->checkers[$type], $value);
    }

    function check_ability($value) {
        global $config;
        if (preg_match('/[!&|()]/', $value))
            return $this->simple_parse($value, array($config->depend, 'check_ability'));
        return gatekeeper($value);
    }

    function check_option($value) {
        global $config;
        if (preg_match('/[!&|()]/', $value))
            return $this->simple_parse($value, array($config->depend, 'check_option'));
        return $config->component == $value || $config->request_component == $value;
    }

    function check_action($value) {
        global $config;
        if (preg_match('/[!&|()]/', $value))
            return $this->simple_parse($value, array($config->depend, 'check_action'));
        return $config->action == $value || $config->request_action == $value;
    }

    function simple_parse($value, $callback) {
        // ex: !val1&&(val2||!val3||(val2&&val4))
        global $config;
        if (preg_match_all('/[^!&|()]+/', $value, $matches)) {
            $search = $replace = array();
            foreach ($matches[0] as $cur_match) {
                $search[] = $cur_match;
                $replace[] = call_user_func($callback, $cur_match) ? 'true' : 'false';
            }
            $parsable = str_replace($search, $replace, $value);
        } else {
            $parsable = call_user_func($callback, $value);
        }
        if (preg_match('/[^!&|()truefals]/', $parsable))
            return false;

        echo $parsable."\n";
        return eval('return '.$parsable.';');
    }
}

?>
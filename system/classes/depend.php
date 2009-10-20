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
 * To add a dependency checker type, assign a callback to the $checkers array.
 *
 * <code>
 * $config->depend->checkers['my_type'] = array($config->run_my_component, 'my_checking_method');
 * </code>
 *
 * Your checker callback should return true if the dependency is satisfied, or
 * false if it is not.
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

    /**
     * Set up the default dependency checker types.
     *
     * - ability (System abilities.)
     * - option (Current or requested component.)
     * - action (Current or requested action.)
     */
    function __construct() {
        global $config;
        $this->checkers['ability'] = array($this, 'check_ability');
        $this->checkers['option'] = array($this, 'check_option');
        $this->checkers['action'] = array($this, 'check_action');
    }

    /**
     * Check a dependency using one of the available checker types.
     *
     * If the requested checker type is not available, check() will return
     * false.
     *
     * @param string $type The type of dependency to be checked.
     * @param mixed $value The value to be checked.
     * @return bool The result of the dependency check.
     */
    function check($type, $value) {
        if (!isset($this->checkers[$type]))
            return false;
        return call_user_func($this->checkers[$type], $value);
    }

    /**
     * Check whether the user has the given ability.
     *
     * Uses simple_parse() to provide simple logic.
     *
     * @uses gatekeeper()
     * @param string $value The value to check.
     * @return bool The result of the ability check.
     */
    function check_ability($value) {
        global $config;
        if (preg_match('/[!&|()]/', $value))
            return $this->simple_parse($value, array($config->depend, 'check_ability'));
        return gatekeeper($value);
    }

    /**
     * Check against the current or requested action.
     *
     * Uses simple_parse() to provide simple logic.
     *
     * @uses $config->action
     * @uses $config->request_action
     * @param string $value The value to check.
     * @return bool The result of the action check.
     */
    function check_action($value) {
        global $config;
        if (preg_match('/[!&|()]/', $value))
            return $this->simple_parse($value, array($config->depend, 'check_action'));
        return $config->action == $value || $config->request_action == $value;
    }

    /**
     * Check against the current or requested component.
     *
     * Uses simple_parse() to provide simple logic.
     *
     * @uses $config->component
     * @uses $config->request_component
     * @param string $value The value to check.
     * @return bool The result of the component check.
     */
    function check_option($value) {
        global $config;
        if (preg_match('/[!&|()]/', $value))
            return $this->simple_parse($value, array($config->depend, 'check_option'));
        return $config->component == $value || $config->request_component == $value;
    }

    /**
     * Parse simple logic statements using a callback.
     *
     * Logic statements can be made with the following operators:
     * - ! (Not)
     * - & (And)
     * - | (Or)
     *
     * They can be grouped using parentheses.
     *
     * For example:
     * <code>
     * simple_parse('!val1&(val2|!val3|(val2&!val4))', array($config->run_my_component, 'my_checking_method'));
     * </code>
     *
     * @param string $value The logic statement.
     * @param callback $callback The callback to check each part with.
     * @return bool The result of the parsing.
     */
    function simple_parse($value, $callback) {
        // ex: !val1&(val2|!val3|(val2&val4))
        // Check whether there are parts, and fill an array with them.
        if (preg_match_all('/[^!&|()]+/', $value, $matches)) {
            $search = $replace = array();
            // For every match, check it and save the result.
            foreach ($matches[0] as $cur_match) {
                $search[] = $cur_match;
                $replace[] = call_user_func($callback, $cur_match) ? 'true' : 'false';
            }
            // Replace each part with its result.
            $parsable = str_replace($search, $replace, $value);
        } else {
            $parsable = call_user_func($callback, $value);
        }

        // If any illegal characters exist, return false.
        if (preg_match('/[^!&|()truefals]/', $parsable))
            return false;

        // Use PHP to evaluate the string.
        return eval('return '.$parsable.';');
    }
}

?>
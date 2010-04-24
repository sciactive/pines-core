<?php
/**
 * depend class.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Dependency checker.
 *
 * To add a dependency checker type, assign a callback to the $checkers array.
 *
 * <code>
 * $pines->depend->checkers['my_type'] = array($pines->com_mycomponent, 'my_checking_method');
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
	 * @var array $checkers
	 */
	public $checkers = array();

	/**
	 * Set up the default dependency checker types.
	 *
	 * - ability (System abilities.)
	 * - action (Current or requested action.)
	 * - class (Class exists.)
	 * - component (Installed enabled components.)
	 * - component_version (Component version.)
	 * - function (Function exists.)
	 * - option (Current or requested component.)
	 * - php_version (PHP version.)
	 * - pines_version (Pines version.)
	 * - service (Available services.)
	 */
	function __construct() {
		global $pines;
		$this->checkers['ability'] = array($this, 'check_ability');
		$this->checkers['action'] = array($this, 'check_action');
		$this->checkers['class'] = array($this, 'check_class');
		$this->checkers['component'] = array($this, 'check_component');
		$this->checkers['component_version'] = array($this, 'check_component_version');
		$this->checkers['function'] = array($this, 'check_function');
		$this->checkers['option'] = array($this, 'check_option');
		$this->checkers['php_version'] = array($this, 'check_php_version');
		$this->checkers['pines_version'] = array($this, 'check_pines_version');
		$this->checkers['service'] = array($this, 'check_service');
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
	public function check($type, $value) {
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
		global $pines;
		if (preg_match('/[!&|()]/', $value))
			return $this->simple_parse($value, array($pines->depend, 'check_ability'));
		return gatekeeper($value);
	}

	/**
	 * Check against the current or requested action.
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @uses $pines->action
	 * @uses $pines->request_action
	 * @param string $value The value to check.
	 * @return bool The result of the action check.
	 */
	function check_action($value) {
		global $pines;
		if (preg_match('/[!&|()]/', $value))
			return $this->simple_parse($value, array($pines->depend, 'check_action'));
		return $pines->action == $value || $pines->request_action == $value;
	}

	/**
	 * Check to see if a class exists.
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @param string $value The value to check for.
	 * @return bool The result of the class check.
	 */
	function check_class($value) {
		global $pines;
		if (preg_match('/[!&|()]/', $value))
			return $this->simple_parse($value, array($pines->depend, 'check_class'));
		return class_exists($value);
	}

	/**
	 * Check if a component is installed and enabled.
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @uses $pines->components
	 * @param string $value The value to check.
	 * @return bool The result of the component check.
	 */
	function check_component($value) {
		global $pines;
		if (preg_match('/[!&|()]/', $value))
			return $this->simple_parse($value, array($pines->depend, 'check_component'));
		$component = preg_replace('/([a-z0-9_]+)([<>=]{1,2})(.+)/S', '$1', $value);
		$compare = preg_replace('/([a-z0-9_]+)([<>=]{1,2})(.+)/S', '$2', $value);
		$required = preg_replace(' /([a-z0-9_]+)([<>=]{1,2})(.+)/S', '$3', $value);
		if ($required == '') {
			return in_array($value, $pines->components);
		} else {
			return version_compare($pines->info->$component->version, $required, $compare);
		}
	}

	/**
	 * Check to see if a function exists.
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @param string $value The value to check.
	 * @return bool The result of the function check.
	 */
	function check_function($value) {
		global $pines;
		if (preg_match('/[!&|()]/', $value))
			return $this->simple_parse($value, array($pines->depend, 'check_function'));
		return function_exists($value);
	}

	/**
	 * Check against the current or requested component.
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @uses $pines->component
	 * @uses $pines->request_component
	 * @param string $value The value to check.
	 * @return bool The result of the component check.
	 */
	function check_option($value) {
		global $pines;
		if (preg_match('/[!&|()]/', $value))
			return $this->simple_parse($value, array($pines->depend, 'check_option'));
		return $pines->component == $value || $pines->request_component == $value;
	}

	/**
	 * Check PHP's version.
	 *
	 * Operators should be placed before the version number to test. Such as,
	 * ">=5.2.10". The available operators are:
	 *
	 * - =
	 * - <
	 * - >
	 * - <=
	 * - >=
	 * - <>
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @param string $value The value to check.
	 * @return bool The result of the version comparison.
	 */
	function check_php_version($value) {
		global $pines;
		if (preg_match('/[!&|()]/', $value))
			return $this->simple_parse($value, array($pines->depend, 'check_php_version'));
		// <, >, =, <=, >=
		$compare = preg_replace('/([<>=]{1,2})(.+)/S', '$1', $value);
		$required = preg_replace('/([<>=]{1,2})(.+)/S', '$2', $value);
		return version_compare(phpversion(), $required, $compare);
	}

	/**
	 * Check Pines' version.
	 *
	 * Operators should be placed before the version number to test. Such as,
	 * ">=1.0.0". The available operators are:
	 *
	 * - =
	 * - <
	 * - >
	 * - <=
	 * - >=
	 * - <>
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @param string $value The value to check.
	 * @return bool The result of the version comparison.check_pines_version
	 */
	function check_pines_version($value) {
		global $pines;
		if (preg_match('/[!&|()]/', $value))
			return $this->simple_parse($value, array($pines->depend, 'check_pines_version'));
		// <, >, =, <=, >=
		$compare = preg_replace('/([<>=]{1,2})(.+)/S', '$1', $value);
		$required = preg_replace('/([<>=]{1,2})(.+)/S', '$2', $value);
		return version_compare($pines->info->version, $required, $compare);
	}

	/**
	 * Check if a service is available.
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @uses $pines->services
	 * @param string $value The value to check.
	 * @return bool The result of the service check.
	 */
	function check_service($value) {
		global $pines;
		if (preg_match('/[!&|()]/', $value))
			return $this->simple_parse($value, array($pines->depend, 'check_component'));
		return key_exists($value, $pines->services);
	}

	/**
	 * Parse simple logic statements using a callback.
	 *
	 * Logic statements can be made with the following operators:
	 * - ! (Bang - Not)
	 * - & (Ampersand - And)
	 * - | (Pipe - Or)
	 *
	 * They can be grouped using parentheses.
	 *
	 * For example:
	 * <code>
	 * simple_parse('!val1&(val2|!val3|(val2&!val4))', array($pines->com_mycomponent, 'my_checking_method'));
	 * </code>
	 *
	 * @param string $value The logic statement.
	 * @param callback $callback The callback to check each part with.
	 * @return bool The result of the parsing.
	 */
	public function simple_parse($value, $callback) {
		// ex: !val1&(val2|!val3|(val2&val4))
		// Check whether there are parts, and fill an array with them.
		if (preg_match_all('/[^!&|()]+/', $value, $matches)) {
			$search = $replace = array();
			// For every match, check it and save the result.
			usort($matches[0], array($this, 'sort_by_length'));
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

	/**
	 * Sort strings from longest to shortest.
	 *
	 * @access private
	 * @param string $a String 1.
	 * @param string $b String 2.
	 * @return int Result.
	 */
	private function sort_by_length($a, $b) {
		return (strlen($b) - strlen($a));
	}
}

?>
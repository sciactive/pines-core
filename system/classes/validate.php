<?php
/**
 * validate class.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Pines Validate PHP class.
 *
 * Pines Validate can validate and transform inputs based on a set of rules.
 *
 * @package Pines
 */
class validate extends p_base {
	/**
	 * An array of validation checker callbacks.
	 *
	 * @var array $checkers
	 */
	public $checkers = array();

	/**
	 * Set up the default checker types.
	 *
	 * - required
	 * - one_required
	 * - alpha
	 * - alphanumeric
	 * - integer
	 * - float
	 * - email
	 * - phone
	 * - ssn
	 */
	function __construct() {
		$this->checkers['required'] = array($this, 'check_required');
	}

	/**
	 * Check that a value is valid.
	 *
	 * If the requested checker type is not available, check() will return
	 * false.
	 *
	 * @param string $type The type to use.
	 * @param mixed $value The value to check.
	 * @return bool The result of the check.
	 */
	function check($type, $value, $args) {
		if (!isset($this->checkers[$type]))
			return false;
		return call_user_func($this->checkers[$type], $value, $args);
	}

	/**
	 * Check a value.
	 *
	 * @param string $value The value to check.
	 * @param mixed $args The arguments.
	 * @return bool The result of the check.
	 */
	function check_required($value, $args) {
		if (empty($value)) {
			pines_notice($args);
			return false;
		}
		return true;
	}
}

?>
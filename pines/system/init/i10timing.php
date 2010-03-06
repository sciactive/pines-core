<?php
/**
 * Load script timing.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (P_SCRIPT_TIMING) {
	/**
	 * Display a message for Pines Script Timing.
	 *
	 * Messages will be displayed in the FireBug console, if available, or an
	 * alert() if not.
	 *
	 * @param string $message The message.
	 * @param bool $print_now Whether to print the page now.
	 */
	function pines_print_time($message, $print_now = false) {
		static $time_output;
		$microtime = microtime(true);
		$time_output .= sprintf(str_pad($message, 40).'%F\n', ($microtime - $GLOBALS['p_last_time']));
		$GLOBALS['p_last_time'] = $microtime;
		if ($print_now) {
			echo '<script type="text/javascript">
(function(message){
	if (console.log) {
		console.log(message);
	} else {
		alert(message);
	}
})("';
			echo 'Pines Script Timing\n\nTimes are measured in seconds.\n';
			echo $time_output;
			printf('--------------------\n'.str_pad('Script Run', 40).'%F', ($microtime - P_EXEC_TIME));
			echo '");</script>';
		}
	}
	$GLOBALS['p_last_time'] = P_EXEC_TIME;
	pines_print_time('Script Timing Start');
}

?>
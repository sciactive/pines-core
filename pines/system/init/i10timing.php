<?php
/**
 * Load script timing.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
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
		static $time_array = array();
		$microtime = microtime(true);
		if (!isset($time_array[$message]))
			$time_array[$message] = array();
		$time_array[$message][] = $microtime;
		if ($print_now) {
			$total_time = $microtime - P_EXEC_TIME;
			foreach($time_array as $message => $times) {
				$time = end($times) - reset($times);
				$percent = $time / $total_time * 100;
				$time_output .= sprintf(str_pad($message, 70).'%.6F (% 5.2F%%)\n', $time, $percent);
			}
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
			printf('--------------------\n'.str_pad('Script Run', 70).'%F', $total_time);
			echo '");</script>';
		}
	}
	pines_print_time('Script Timing Start');
	pines_print_time('Script Timing Start');
}

?>
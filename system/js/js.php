<?php
/**
 * Provide the default JavaScript files, concatenated.
 *
 * This file also fills in the full_location and rela_location variables in the
 * JavaScript pines object.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */

header('Content-Type: text/javascript');

$mod_date = 0;
foreach(array('common.js', 'json2.js', 'jquery.min.js', 'jquery-ui.min.js', 'jquery.pnotify.js') as $cur_file) {
	$cur_mod_date = filemtime($cur_file);
	$mod_date = $mod_date > $cur_mod_date ? $mod_date : $cur_mod_date;
}

if (array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER) && $mod_date <= strtotime(preg_replace('/;.*$/', '', $_SERVER["HTTP_IF_MODIFIED_SINCE"]))) {
	header('x', TRUE, 304);
	exit;
}

$exclude = explode(' ', $_REQUEST['exclude']);

$output =
(!in_array('common.js', $exclude) ? file_get_contents('common.js')."\n" : '').
"pines.full_location = \"".'http'.(($_SERVER["HTTPS"] == "on") ? 's://' : '://').$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strripos($_SERVER['PHP_SELF'], 'system/js/js.php'))."\"\n".
"pines.rela_location = \"".substr($_SERVER['PHP_SELF'], 0, strripos($_SERVER['PHP_SELF'], 'system/js/js.php'))."\"\n".
(!in_array('json2.js', $exclude) ? "\n".file_get_contents('json2.js') : '').
(!in_array('jquery.min.js', $exclude) ? "\n".file_get_contents('jquery.min.js') : '').
(!in_array('jquery-ui.min.js', $exclude) ? "\n".file_get_contents('jquery-ui.min.js') : '').
(!in_array('jquery.pnotify.js', $exclude) ? "\n".file_get_contents('jquery.pnotify.js') : '')
;

// I've decided that slightly shorter load times aren't worth risking mangled JavaScript.
// Maybe in the future...

// Strip comments. (Flawed)
//$output = preg_replace('/(\'.*?[^\/]\')|(".*?[^\/]")|(\/\*[\x{0000}-\x{FFFF}]*?(?=\*\/)\*\/|\/\/[^\x{000A}|\x{000D}|\x{2028}|\x{2029}]*)/su', "$1$2", $output);
// Compress long white space.
//$output = preg_replace('/[ \t]{2,}/m', " ", $output);
// Compress multiple new lines.
//$output = preg_replace('/\n\s*\n/m', "\n", $output);

header('Content-Length: '.strlen($output));
header('Cache-Control: public');
header('Pragma:');
header('Expires: '.date('r', strtotime('+3 days')));
header('Last-Modified: '.date('r', $mod_date));

echo $output;

?>
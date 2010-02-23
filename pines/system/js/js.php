<?php
/**
 * Provide the default JavaScript files, concatenated.
 *
 * This file also fills in the full_location and rela_location variables in the
 * JavaScript pines object and includes a JSON object, if one is not available.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */

header('Content-Type: text/javascript');

if (array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER) && filemtime('common.js') <= strtotime(preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']))) {
	header('x', TRUE, 304);
	exit;
}

$output =
file_get_contents('common.js')."\n".
'pines.full_location = "http'.(($_SERVER['HTTPS'] == 'on') ? 's://' : '://').$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strripos($_SERVER['PHP_SELF'], 'system/js/js.php'))."\"\n".
'pines.rela_location = "'.substr($_SERVER['PHP_SELF'], 0, strripos($_SERVER['PHP_SELF'], 'system/js/js.php'))."\"\n".
"if(!this.JSON){pines.loadjs(pines.rela_location+\"system/js/json2.js\");}\n";

header('Content-Length: '.strlen($output));
header('Cache-Control: public');
header('Pragma:');
header('Expires: '.date('r', strtotime('+3 days')));
header('Last-Modified: '.date('r', $mod_date));

echo $output;

?>
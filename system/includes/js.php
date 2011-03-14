<?php
/**
 * Provide the default JavaScript files, concatenated.
 *
 * This file also fills in the full_location and rela_location variables in the
 * JavaScript pines object and includes a JSON object, if one is not available.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

header('Content-Type: text/javascript');

$mod_date = filemtime('pines.min.js');
$etag = dechex(crc32($mod_date));

if (
		(array_key_exists('HTTP_IF_NONE_MATCH', $_SERVER) && strpos($_SERVER['HTTP_IF_NONE_MATCH'], $etag) !== false ) ||
		(array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER) && $mod_date <= strtotime(preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE'])))
	) {
	header('Content-Type: ');
	header('ETag: "'.$etag.'"');
	header('X', true, 304);
	exit;
}

$output =
file_get_contents('pines.min.js')."\n".
'pines.full_location = "http'.(($_SERVER['HTTPS'] == 'on') ? 's://' : '://').$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strripos($_SERVER['PHP_SELF'], 'system/includes/js.php'))."\"\n".
'pines.rela_location = "'.substr($_SERVER['PHP_SELF'], 0, strripos($_SERVER['PHP_SELF'], 'system/includes/js.php'))."\"\n".
"if(!this.JSON){pines.loadjs(pines.rela_location+\"system/includes/json2.js\");}\n";

header('Content-Length: '.strlen($output));
header('Last-Modified: '.date('r', $mod_date));
header('ETag: "'.$etag.'"');

echo $output;

?>
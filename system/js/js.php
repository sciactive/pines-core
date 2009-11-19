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

echo file_get_contents('common.js').
"\n".
"pines.full_location = \"".'http'.(($_SERVER["HTTPS"] == "on") ? 's://' : '://').$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strripos($_SERVER['PHP_SELF'], 'system/js/js.php'))."\"\n".
"pines.rela_location = \"".substr($_SERVER['PHP_SELF'], 0, strripos($_SERVER['PHP_SELF'], 'system/js/js.php'))."\"\n".
"\n".
file_get_contents('json2.js').
"\n".
file_get_contents('jquery.min.js').
"\n".
file_get_contents('jquery-ui.min.js')
;

?>
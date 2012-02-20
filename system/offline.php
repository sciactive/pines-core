<?php
/**
 * Displays the offline message and the page title.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
header('Content-Type: text/html');
?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title><?php echo htmlspecialchars($this->config->page_title); ?></title>
	<style type="text/css" media="all">
		.wrapper {
			margin: 3em;
			font-family: sans;
			font-size: 80%;
		}
		.wrapper fieldset {
			border: 1px solid #040;
			-moz-border-radius: 10px;
		}
		.wrapper legend {
			padding: 0.5em 0.8em;
			border: 2px solid #040;
			color: #040;
			font-size: 120%;
			-moz-border-radius: 10px;
		}
		.wrapper label {
			display: block;
			text-align: right;
			margin-right: 60%;
		}
		.wrapper input {
			color: #040;
		}
		.wrapper .buttons {
			text-align: right;
		}
	</style>
</head>
<body>
<div class="wrapper">
	<fieldset>
		<legend><?php echo htmlspecialchars($this->config->system_name); ?></legend>
		<p><?php echo $this->config->offline_message; ?></p>
	</fieldset>
</div>
</body>
</html>
<?php exit; ?>
<?php
/**
 * Displays a note about the template and the page title.
 *
 * @package Core
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
header('HTTP/1.1 503 Service Unavailable');
header('Content-Type: text/html');
?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title><?php echo htmlspecialchars($this->config->page_title); ?></title>
	<link href='http://fonts.googleapis.com/css?family=EB+Garamond' rel='stylesheet' type='text/css'>
	<style type="text/css" media="all">
		html {
			font-size: 100%;
			-webkit-text-size-adjust: 100%;
			-ms-text-size-adjust: 100%;
		}
		body {
			margin: 0;
			font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
			font-size: 18px;
			line-height: 22px;
			color: #333;
			background-color: #fff;
			text-rendering: optimizelegibility;
		}
		.wrapper {
			margin: 2em;
		}
		.wrapper fieldset {
			border: 1px dashed rgba(82, 168, 236, 0.8);
			padding: 0 1em 1em;
		}
		.wrapper legend {
			font-family: 'EB Garamond', serif;
			padding: 0 .2em;
			font-size: 72px;
			line-height: 1;
			border: none;
		}
		.wrapper p {
			margin: 1em 0 0;
			padding: 0;
		}
		.wrapper label {
			margin: 1em 0 0;
			display: block;
			text-align: right;
			margin-right: 60%;
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
		<p>The currently selected template is either missing or is not compatible with <?php echo htmlspecialchars($this->info->name); ?>.</p>
	</fieldset>
</div>
</body>
</html>
<?php exit; ?>
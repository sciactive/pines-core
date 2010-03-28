<?php
/**
 * Displays a note about the template and the page title.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title><?php echo $pines->config->option_title; ?></title>
	<style type="text/css" media="all">
		/* <![CDATA[ */
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
		/* ]]> */
	</style>
</head>
<body>
<div class="wrapper">
	<fieldset>
		<legend><?php echo $pines->config->option_title; ?></legend>
		<p>The currently selected template is either missing or is not compatible with <?php echo $pines->info->name; ?>.</p>
	</fieldset>
</div>
</body>
</html>
<?php exit; ?>
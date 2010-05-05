<?php
/**
 * Pines' configuration.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'full_location',
		'cname' => 'Full Location',
		'description' => 'The URL of this Pines installation. End this path with a slash!',
		'value' => 'http'.(($_SERVER['HTTPS'] == "on") ? 's://' : '://').$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strripos($_SERVER['PHP_SELF'], P_INDEX)),
	),
	array(
		'name' => 'rela_location',
		'cname' => 'Relative Location',
		'description' => 'The URL location of Pines relative to your server root. If it is in the root of the server, just put a slash (/). End this path with a slash!',
		'value' => substr($_SERVER['PHP_SELF'], 0, strripos($_SERVER['PHP_SELF'], P_INDEX)),
	),
	array(
		'name' => 'setting_upload',
		'cname' => 'Upload Directory',
		'description' => 'The directory to store uploaded files. This should be the real, relative path and the relative URL. End this path with a slash!',
		'value' => 'media/',
		'peruser' => true,
	),
	array(
		'name' => 'offline_mode',
		'cname' => 'Offline Mode',
		'description' => 'In offline mode, the system will not be accessible, and simply display the offline message. To disable offline mode, you will need to edit "system/config.php" in the Pines directory. Under the array named "offline_mode", change the value to false.',
		'value' => false,
	),
	array(
		'name' => 'offline_message',
		'cname' => 'Offline Message',
		'description' => 'The message to display when in offline mode.',
		'value' => 'We are currently offline for maintenance. Please try back shortly.',
	),
	array(
		'name' => 'option_title',
		'cname' => 'Page Title',
		'description' => 'The default title at the top of each page.',
		'value' => 'Pines',
		'peruser' => true,
	),
	array(
		'name' => 'option_copyright_notice',
		'cname' => 'Copyright Notice',
		'description' => 'The copyright notice at the bottom of each page.',
		'value' => '&copy; 2010 SciActive.com. All Rights Reserved. Powered by <a href="http://pines.sourceforge.net/" onclick="window.open(this.href); return false;">Pines</a>.',
		'peruser' => true,
	),
	array(
		'name' => 'default_template',
		'cname' => 'Default Template',
		'description' => 'The default template.',
		'value' => 'tpl_pines',
		'options' => pines_scandir('templates/'),
		'peruser' => true,
	),
	array(
		'name' => 'allow_template_override',
		'cname' => 'Template Override',
		'description' => 'Allow the template to be overriden by adding ?template=tpl_whatever',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'url_rewriting',
		'cname' => 'URL Rewriting',
		'description' => 'Use url rewriting engine.',
		'value' => false,
	),
	array(
		'name' => 'use_htaccess',
		'cname' => 'Apache .htaccess',
		'description' => 'Use Apache .htaccess with mod_rewrite. (Rename htaccess.txt to .htaccess before using.)',
		'value' => false,
	),
	array(
		'name' => 'default_component',
		'cname' => 'Default Component',
		'description' => 'This component should have a "default" action. That action will be called when the user first accesses Pines. If an action is specified, but no component, this one will be used.',
		'value' => 'com_user',
		'options' => pines_scandir('components/'),
	),
	array(
		'name' => 'timezone',
		'cname' => 'System Timezone',
		'description' => 'The timezone the system should use as its default. User\'s timezones will default to this.',
		'value' => date_default_timezone_get(),
	),
	array(
		'name' => 'debug_mode',
		'cname' => 'Debug Mode',
		'description' => 'Only use debug mode during testing. When debug mode is enabled, components will use non-minified versions of their JavaScripts (if available), in order to make debugging easier.',
		'value' => false,
		'peruser' => true,
	),
);

?>
<?php
/**
 * Pines' configuration.
 *
 * @package Core
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$templates = pines_scandir('templates/');
return array(
	array(
		'name' => 'system_name',
		'cname' => 'System Name',
		'description' => 'The name of the system.',
		'value' => 'Pines',
		'peruser' => true,
	),
	array(
		'name' => 'full_location',
		'cname' => 'Full Location',
		'description' => 'The URL of this Pines installation. If you leave this blank, it will be discovered for you. End this path with a slash!',
		'value' => '',
	),
	array(
		'name' => 'rela_location',
		'cname' => 'Relative Location',
		'description' => 'The URL location of Pines relative to your server root. If it is in the root of the server, just put a slash (/). If you leave this blank, it will be discovered for you. End this path with a slash!',
		'value' => '',
	),
	array(
		'name' => 'static_location',
		'cname' => 'Static Location',
		'description' => 'The URL of this Pines installation, for access of static content, like CSS and images. End this path with a slash!',
		'value' => '',
	),
	array(
		'name' => 'upload_location',
		'cname' => 'Upload Location',
		'description' => 'The location to store uploaded files. This should be the real, relative path and the relative (to "Full Location") URL. End this path with a slash!',
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
		'value' => 'We are currently offline for maintenance. Please try back shortly.<br /><br />In the meantime, you can watch our Twitter feed to the right for updates.',
	),
	array(
		'name' => 'offline_twitter_feed',
		'cname' => 'Offline Twitter Feed',
		'description' => 'An optional Twitter username to display tweets from while offline.',
		'value' => 'pinesframework',
	),
	array(
		'name' => 'page_title',
		'cname' => 'Page Title',
		'description' => 'The default title at the top of each page.',
		'value' => 'Pines',
		'peruser' => true,
	),
	array(
		'name' => 'copyright_notice',
		'cname' => 'Copyright Notice',
		'description' => 'The copyright notice at the bottom of each page.',
		'value' => '&copy; 2011-2012 SciActive.com. All Rights Reserved. Powered by Pines.',
		'peruser' => true,
	),
	array(
		'name' => 'template_override',
		'cname' => 'Template Override',
		'description' => 'Allow the template to be overridden by adding ?template=tpl_whatever',
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
		'description' => 'Use Apache .htaccess with mod_rewrite to hide "index.php" in the URL. (Rename htaccess.txt to .htaccess before using.)',
		'value' => false,
	),
	array(
		'name' => 'default_template',
		'cname' => 'Default Template',
		'description' => 'The default template.',
		'value' => 'tpl_pinescms',
		'options' => $templates,
		'peruser' => true,
	),
	array(
		'name' => 'admin_template',
		'cname' => 'Admin Template',
		'description' => 'The default template for administrators. (Users who have the system/all ability.)',
		'value' => 'tpl_pines',
		'options' => $templates,
		'peruser' => true,
	),
	array(
		'name' => 'default_component',
		'cname' => 'Default Component',
		'description' => 'This component should have a "default" action. That action will be called when the user first accesses the system. If an action is specified, but no component, this one will be used.',
		'value' => 'com_dash',
		'options' => is_callable(array($this, 'get_default_components')) ? $this->get_default_components() : $pines->config->get_default_components(),
		'peruser' => true,
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
        array(
		'name' => 'compress_cssjs',
		'cname' => 'Compress CSS/JS on template AND components',
		'description' => 'Bootstrap, jquery, pnotify, pform, icons, etc will be compressed when this option and compatible template is used.',
		'value' => false,
		'peruser' => true,
	),
        array(
		'name' => 'compressed_url_root',
		'cname' => 'URL root for compressed CSS/JS',
		'description' => 'Best practice to use alternate domain to serve compressed files. Also depending on template, the url to build the styles could be changed to use another site.',
		'value' => $_SERVER['DOCUMENT_ROOT'],
		'peruser' => true,
	),
);

?>
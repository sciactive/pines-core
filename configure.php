<?php
/**
 * Pines' configuration.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array (
  0 =>
  array (
    'name' => 'full_location',
    'cname' => 'Full Location',
    'description' => 'The URL of this Pines installation. End this path with a slash!',
    'value' => 'http'.(($_SERVER["HTTPS"] == "on") ? 's://' : '://').$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strripos($_SERVER['PHP_SELF'], 'index.php')),
  ),
  1 =>
  array (
    'name' => 'rela_location',
    'cname' => 'Relative Location',
    'description' => 'The URL location of Pines relative to your server root. If it is in the root of the server, just put a slash (/). End this path with a slash!',
    'value' => substr($_SERVER['PHP_SELF'], 0, strripos($_SERVER['PHP_SELF'], 'index.php')),
  ),
  2 =>
  array (
    'name' => 'setting_upload',
    'cname' => 'Upload Directory',
    'description' => 'The directory to store uploaded files. This should be the real, relative path and the relative URL. End this path with a slash!',
    'value' => 'media/',
  ),
  3 =>
  array (
    'name' => 'offline_mode',
    'cname' => 'Offline Mode',
    'description' => 'In offline mode, the system will not be accessible, and simply display the offline message. To disable offline mode, you will need to edit "configure.php" in the Pines directory. Under the array named "offline_mode", change the value to false.',
    'value' => false,
  ),
  4 =>
  array (
    'name' => 'offline_message',
    'cname' => 'Offline Message',
    'description' => 'The message to display when in offline mode.',
    'value' => 'We are currently offline for maintenance. Please try back shortly.',
  ),
  5 =>
  array (
    'name' => 'option_title',
    'cname' => 'Page Title',
    'description' => 'The default title at the top of each page.',
    'value' => 'Pines',
  ),
  6 =>
  array (
    'name' => 'option_copyright_notice',
    'cname' => 'Copyright Notice',
    'description' => 'The copyright notice at the bottom of each page.',
    'value' => '&copy; 2009 Hunter Perrin. All Rights Reserved.<br />Powered by Pines.',
  ),
  7 =>
  array (
    'name' => 'default_template',
    'cname' => 'Default Template',
    'description' => 'The default template.',
    'value' => 'pines',
  ),
  8 =>
  array (
    'name' => 'allow_template_override',
    'cname' => 'Template Override',
    'description' => 'Allow the template to be overriden by adding ?template=whatever',
    'value' => true,
  ),
  9 =>
  array (
    'name' => 'url_rewriting',
    'cname' => 'URL Rewriting',
    'description' => 'Use url rewriting engine.',
    'value' => true,
  ),
  10 =>
  array (
    'name' => 'use_htaccess',
    'cname' => 'Apache .htaccess',
    'description' => 'Use Apache .htaccess with mod_rewrite. (Rename htaccess.txt to .htaccess before using.)',
    'value' => false,
  ),
  11 =>
  array (
    'name' => 'default_component',
    'cname' => 'Default Component',
    'description' => 'This component should have a "default" action. That action will be called when the user first accesses Pines. If an action is specified, but no component, this one will be used.',
    'value' => 'com_user',
  ),
  12 =>
  array (
    'name' => 'program_title',
    'cname' => 'Program Name',
    'description' => 'The program\'s internal name.',
    'value' => 'Pines',
  ),
  13 =>
  array (
    'name' => 'program_version',
    'cname' => 'Program Version',
    'description' => 'The program\'s internal version number. Changing this may cause problems while updating!',
    'value' => '0.14 Alpha',
  ),
);

?>
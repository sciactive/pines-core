<?php
/**
 * Initialize the Pines system.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * The current template.
 *
 * @global string $pines->current_template
 */
$pines->current_template = ( !empty($_REQUEST['template']) && $pines->config->allow_template_override ) ?
	$_REQUEST['template'] : $pines->config->default_template;
$pines->template = $pines->current_template;
date_default_timezone_set($pines->config->timezone);
if (P_SCRIPT_TIMING) pines_print_time('Load System Config');

// Check the offline mode, and load the offline page if enabled.
if ($pines->config->offline_mode)
	require('system/offline.php');

/**
 * An array of the enabled components.
 * @global array $pines->components
 */
$pines->components = array();
/**
 * An array of all components.
 * @global array $pines->all_components
 */
$pines->all_components = array();
if ( file_exists('components/') && file_exists('templates/') ) {
	$pines->components = array_merge(pines_scandir('components/'), pines_scandir('templates/'));
	$pines->all_components = array_merge(pines_scandir('components/', 0, null, false), pines_scandir('templates/', 0, null, false));
	foreach ($pines->all_components as &$cur_value) {
		if (substr($cur_value, 0, 1) == '.')
			$cur_value = substr($cur_value, 1);
	}
	unset($cur_value);
	sort($pines->components);
	sort($pines->all_components);
}

// Load component classes.
/**
 * List of class files for autoloading classes.
 *
 * Note that templates have a classes dir, but the only file loaded from it is
 * the file of the same name as the template. Also, only the current template's
 * class is loaded.
 *
 * @var array $pines->class_files
 */
$pines->class_files = array();
$temp_classes = glob('components/com_*/classes/*.php');
foreach ($temp_classes as $cur_class) {
	$pines->class_files[preg_replace('/^\/|\.php$/', '', strrchr($cur_class, '/'))] = $cur_class;
}
unset($cur_class);
unset($temp_classes);
// If the current template is missing its class, display the template error page.
if ( !file_exists("templates/{$pines->current_template}/classes/{$pines->current_template}.php") )
	require('system/template_error.php');
$pines->class_files[$pines->current_template] = "templates/{$pines->current_template}/classes/{$pines->current_template}.php";
if (P_SCRIPT_TIMING) pines_print_time('Find Component Classes');
/**
 * Load a class file.
 *
 * @param string $class_name The class name.
 */
function __autoload($class_name) {
	global $pines;
	// When session_start() tries to recover hooked objects, we need to make
	// sure their equivalent hooked classes exist.
	if (strpos($class_name, 'hook_override_') === 0) {
		$trace = debug_backtrace();
		// But the hook object will check if a hooked class exists before
		// hooking it, so we don't want to create an extra object each time.
		if ($trace[1]['function'] == 'class_exists')
			return;
		$new_class = preg_replace('/^hook_override_/', '', $class_name);
		$new_object = new $new_class;
		$pines->hook->hook_object($new_object, get_class($new_object).'->', false);
		unset($new_object);
		return;
	}
	if (key_exists($class_name, $pines->class_files)) {
		include_once($pines->class_files[$class_name]);
		if (P_SCRIPT_TIMING) pines_print_time("Load [$class_name]");
	}
}

// Now that all classes can be loaded, we can start the session manager. This
// allows variables to keep their classes over sessions.
session_start();

// Load the hooks for $pines.
$pines->hook->hook_object($pines, '$pines->');
if (P_SCRIPT_TIMING) pines_print_time('Hook $pines');

?>
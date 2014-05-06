<?php
/*
 * Get components and their classes
 * 
 * @package Core
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author1 Angela Murrell <amasiell.g@gmail.com>
 * @author2 Mohammed Ahmed <mohammedsadikahmed@gmail.com>
 * @author3 Grey Vugrin <greyvugrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 * 
 * Generate 1 file in this directory. 
 * Requires manual updating.
 * 
 * Good practice could be to execute this file from a bash script,
 * perhaps including the git pull or other repo updating commands
 * in that same script - ensuring every update will remake this file.
 * 
 * If you do not generate this file, no worries. Enjoy slower pines.
 * If you do not have STDIN, also, oh well.
 * pines.php system class will check for this file and use it instead.
 * 
 * IMPORTANT: Run this file from /your/path/installationfolder/
 * ie. $~me/htdocs/pines/   php system/get_component_classes.php
 * aka. One level up from system. (avoids problems with symbolic links).
*/

// Gatekeeper: Do not run except from terminal (bash script?)
if (!defined("STDIN")) {
	header("HTTP/1.0 404 Not Found");
	echo "404 Not Found";
	exit;
}

// Scandir
function pines_scandir($directory, $sorting_order = 0, $context = null, $hide_dot_files = true) {
	if (isset($context)) {
		if (!($return = scandir($directory, $sorting_order, $context)))
			return false;
	} else {
		if (!($return = scandir($directory, $sorting_order)))
			return false;
	}
	foreach ($return as $cur_key => $cur_name) {
		if ( (stripos($cur_name, '.') === 0 && $hide_dot_files) || (in_array($cur_name, array('index.html', '.', '..', '.svn'))) )
			unset($return[$cur_key]);
	}
	return array_values($return);
}

// Get Components.
if ( file_exists('components/') && file_exists('templates/') ) {
	$components = array();
	$all_components = array_merge(pines_scandir('components/', 0, null, false), pines_scandir('templates/', 0, null, false));
	foreach ($all_components as &$cur_value) {
		if (substr($cur_value, 0, 1) == '.') {
			$cur_value = substr($cur_value, 1);
		} else {
			$components[] = $cur_value;
		}
	}
	sort($components);
	sort($all_components);
}

// Fill the list of component classes.
$temp_classes = glob('components/com_*/classes/*.php');
$class_files = array();
foreach ($temp_classes as $cur_class) {
	$cur_name = strrchr($cur_class, '/');
	$cur_name = substr($cur_name, 1, strlen($cur_name) -5);
	$class_files[$cur_name] = $cur_class;
}

$gen_file = array('all' => $all_components, 'components' => $components, 'classes' => $class_files);

$file_contents = sprintf("<?php\nreturn %s;\n?>",
	var_export($gen_file, true)
);
file_put_contents('system/component_classes.php', $file_contents);
if (file_exists('system/component_classes.php')) {
	echo 'The component classes file exists and was last modified: '.date('l, F j, Y g:i A T', filemtime('system/component_classes.php'))."\n";
} else {
	echo 'The component classes file was not created.'."\n";
}
?>

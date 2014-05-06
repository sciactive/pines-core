<?php
/*
 * Get component inits. 
 * 
 * @package Core
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author1 Angela Murrell <amasiell.g@gmail.com>
 * @author2 Mohammed Ahmed <mohammedsadikahmed@gmail.com>
 * @author3 Grey Vugrin <greyvugrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 * 
 * Generate 1 file in this directory with all init code. 
 * Requires manual updating.
 * 
 * Good practice could be to execute this file from a bash script,
 * perhaps including the git pull or other repo updating commands
 * in that same script - ensuring every update will remake this file.
 * 
 * If you do not generate this file, no worries. Enjoy slower pines.
 * If you do not have STDIN, also, oh well.
 * i50init_components system init will check for this file and use it instead.
 * 
 * IMPORTANT: Run this file from /your/path/installationfolder/
 * ie. $~me/htdocs/pines/   php system/get_component_inits.php
 * aka. One level up from system. (avoids problems with symbolic links).
*/

// Gatekeeper: Do not run except from terminal (bash script?)
if (!defined("STDIN")) {
	header("HTTP/1.0 404 Not Found");
	echo "404 Not Found";
	exit;
}

// Run the component init scripts.
$_p_cominit = glob('components/com_*/init/i*.php');
// Include the common system functions.
$_p_cominit[] = 'system/i01common.php';
// Sort by just the filename.
function pines_sort_by_filename($a, $b) {
	$str1 = strrchr($a, '/');
	$str2 = strrchr($b, '/');
	if ($str1 == $str2) {
		if ($a < $b)
			return -1;
		if ($a > $b)
			return 1;
		return 0;
	} else {
		if ($str1 < $str2)
			return -1;
		if ($str1 > $str2)
			return 1;
		return 0;
	}
}
usort($_p_cominit, 'pines_sort_by_filename');
$content = "<?php\ndefined('P_RUN') or die('Direct access prohibited');\n";
$count = 0;
foreach ($_p_cominit as $_p_cur_cominit) {
	try {
		// Get File
		$cur_content = file_get_contents($_p_cur_cominit);
		// Find all returns and rewrite them:
		$cur_content = preg_replace('/(^if.*?)return;\s*\}/ms', "$1define('HELLOWORLD', true);//usedtoreturn\n} else {", $cur_content);
		$cur_content = preg_replace('/(^if.*?)return;\s*\}/ms', "$1define('HELLOWORLD', true);//usedtoreturn\n} else {", $cur_content);
		$cur_content = preg_replace('/(^if.*?\n\t)return;/ms', "$1define('HELLOWORLD', true);//usedtoreturn\nelse {", $cur_content);
		
		preg_match_all('/usedtoreturn/', $cur_content, $matches);
		
		foreach ($matches[0] as $cur_match) {
			$cur_content = preg_replace('/\?>/m', "}\n?>", $cur_content);
		}
		
		// Get rid of extra php tags, defined P_RUN die
		$cur_content = preg_replace("/<\?php|\?>|^defined.*prohibited'\);/m", '', $cur_content);
		// Get rid of php tags
		$content .= $cur_content;
	} catch (HttpClientException $e) {
		echo 'Client error on file '.$_p_cur_cominit.': '.$e;
	} catch (HttpServerException $e) {
		echo 'Server error on file '.$_p_cur_cominit.': '.$e;
	}
}
unset ($_p_cominit, $_p_cur_cominit);
$content .= "\n?>";

file_put_contents('system/component_inits.php', $content);

if (file_exists('system/component_inits.php')) {
	echo 'The component init file exists and was last modified: '.date('l, F j, Y g:i A T', filemtime('system/component_inits.php'))."\n";
} else {
	echo 'The component init file was not created.'."\n";
}
?>
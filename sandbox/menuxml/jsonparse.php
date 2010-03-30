<?php

$menu_json = json_decode(file_get_contents('menu.json'), true);

$menus = array();

foreach ($menu_json as $cur_entry) {
	$tmp_path = explode('/', $cur_entry['path']);
	$cur_menus =& $menus;
	do {
		if (!key_exists($tmp_path[0], $cur_menus))
			$cur_menus[$tmp_path[0]] = array();
		$cur_menus =& $cur_menus[$tmp_path[0]];
		$tmp_path = array_slice($tmp_path, 1);
	} while (count($tmp_path));
	$cur_menus[0] = $cur_entry;
}

check_depend($menus);

var_export($menus);

function check_depend(&$array, $allow_empty = true) {
	if (!$allow_empty && !$array[0])
		return false;
	if ($array[0]['depend']) {
		foreach ($array[0]['depend'] as $key => $value) {
			if (!pines_depend($key, $value))
				return false;
		}
	}
	foreach ($array as $key => &$value) {
		if (!is_int($key) && !check_depend($value, false))
			unset($array[$key]);
	}
	return true;
}


function pines_depend($type, $value) {
	if ($type == 'ability' && $value == 'com_configure/use&(com_configure/view|com_configure/edit)')
		return false;
	return true;
}

?>
<?php
/**
 * The controller for Pines' architecture.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

/*
 * Pines - a Lightweight PHP Application Framework
 * Copyright (C) 2008-2009  Hunter Perrin.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Hunter can be contacted at hunter@sciactive.com
 *
 */

/**
 * Constants
 */
/**
 * The microtime when the script started executing.
 */
define('P_EXEC_TIME', microtime(true));
/**
 * Declare that the program is running through the index, as it should.
 */
define('P_RUN', true);
/**
 * The installation's base path.
 */
define('P_BASE_PATH', dirname(__FILE__));
/**
 * The name of our index file.
 */
define('P_INDEX', basename($_SERVER['SCRIPT_FILENAME']));
/**
 * When this is set to true, the times between script stages will be displayed.
 *
 * Note that the times can be misleading if a lot of processing happens before
 * an event is logged.
 */
define('P_SCRIPT_TIMING', false);

// Run the system init scripts.
$_p_sysinit = glob('system/init/i*.php');
foreach ($_p_sysinit as $_p_cur_sysinit) {
	require($_p_cur_sysinit);
}

//echo microtime(true) - P_EXEC_TIME;

?>
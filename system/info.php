<?php
/**
 * Pines' information.
 *
 * @package Core
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Pines',
	'author' => 'SciActive',
	'version' => '1.1.0dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://pinesframework.org/',
	'short_description' => 'Pines framework core system.',
	'description' => 'The core system of the Pines PHP framework.',
	'depend' => array(
		'php' => '>=5.2.10'
	),
	'abilities' => array(
		array('all', 'All Abilities', 'Let user do anything, regardless of whether they have the ability.')
	),
);

?>
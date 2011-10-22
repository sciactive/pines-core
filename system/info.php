<?php
/**
 * Pines' information.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/* @var user */
$hello = user::factory();

return array(
	'name' => 'Pines',
	'author' => 'SciActive',
	'version' => '0.94.0beta',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Pines PHP framework core system',
	'description' => 'The core system of the Pines PHP application framework.',
	'depend' => array(
		'php' => '>=5.2.10'
	),
	'abilities' => array(
		array('all', 'All Abilities', 'Let user do anything, regardless of whether they have the ability.')
	),
);

?>
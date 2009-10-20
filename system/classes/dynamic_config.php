<?php
/**
 * dynamic_config class.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * An empty class for arbitrary data.
 *
 * You should not use p_base to hold arbitrary data. Using a separate class for
 * this purpose allows a vendor to extend the config objects without extending
 * other objects, like components, modules, etc.
 *
 * @package Pines
 */
class dynamic_config extends p_base { }

?>
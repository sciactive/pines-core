<?php
/**
 * Pines' base class.
 *
 * @package Pines
 * @subpackage subpackage
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A base for all classes which need hooking support.
 * @package Pines
 */
class p_base {
    public function _p_get($name, $secret) {
        if ( isset($this->$name) && ($secret == $_SESSION['secret']) )
            return $this->$name;
    }

    public function _p_set($name, $value, $secret){
        if ( $secret == $_SESSION['secret'] )
            return ($this->$name = $value);
    }

    public function _p_unset($name, $secret) {
        unset($this->$name);
    }
}

?>
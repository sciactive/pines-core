<?php
/**
 * Save the state of a Pines Grid.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper()) {
    $config->user_manager->punt_user("You don't have necessary permission.");
    return;
}
$cur_state = stripslashes($_REQUEST['state']);
$cur_view = stripslashes($_REQUEST['view']);
if (isset($_SESSION['user'])) {
    if (!is_array($_SESSION['user']->pgrid_saved_states))
        $_SESSION['user']->pgrid_saved_states = array();
    $_SESSION['user']->pgrid_saved_states[$cur_view] = $cur_state;
    $_SESSION['user']->save();
}
$page->override = true;

?>
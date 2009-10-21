<?php
/**
 * A generic 404 error notice.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<p>The page you requested cannot be found on this server.</p>
<p>Suggestions:
    <ul>
        <li>Check the spelling of the address you requested.</li>
        <li>If you are still having problems, please <a href="<?php echo pines_url(); ?>">visit the homepage.</a></li>
    </ul>
</p>
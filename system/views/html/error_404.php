<?php
/**
 * A generic 404 error notice.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Error 404';
$this->note = 'Page not Found.';
?>
<p>The page you requested cannot be found on this server.</p>
<div>Suggestions:
	<ul>
		<li>Check the spelling of the address you requested.</li>
		<li>If you are still having problems, please <a href="<?php echo htmlspecialchars(pines_url()); ?>">visit the homepage</a>.</li>
	</ul>
</div>
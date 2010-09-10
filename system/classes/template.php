<?php
/**
 * template class.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * The template base class.
 *
 * Templates should extend this class.
 * 
 * @package Pines
 */
class template extends p_base implements template_interface {
	/**
	 * The template format.
	 * @var string $format
	 */
	public $format = '';
	/**
	 * The editor CSS location, relative to Pines' directory.
	 * @var string $editor_css
	 */
	public $editor_css = '';

	/**
	 * Format a menu.
	 *
	 * This function is just to satisfy the template interface. It must be
	 * redifined by the template.
	 *
	 * @param array $menu The menu.
	 * @return string An empty string.
	 */
	public function menu($menu) {
		return '';
	}

	/**
	 * Return a URL in the necessary format to be usable on the current
	 * installation.
	 *
	 * url() is designed to work with the URL rewriting features of Pines,
	 * so it should be called whenever outputting a URL is required. If url() is
	 * called with no parameters, it will return the URL of the index page.
	 *
	 * @param string $component The component the URL should point to.
	 * @param string $action The action the URL should point to.
	 * @param array $params An array of parameters which should be part of the URL's query string.
	 * @param bool $full_location Whether to return an absolute URL or a relative URL.
	 * @return string The URL in a format to work with the current configuration of Pines.
	 */
	public function url($component = null, $action = null, $params = array(), $full_location = false) {
		global $pines;
		if ( !$params ) $params = array();
		if ( !isset($params['template']) && isset($_REQUEST['template']) && $pines->config->template_override )
			$params['template'] = $_REQUEST['template'];
		$return = ($full_location) ? $pines->config->full_location : $pines->config->rela_location;
		if ( !isset($component) && !$params )
			return $return;
		if ( $pines->config->url_rewriting ) {
			if ( !$pines->config->use_htaccess )
				$return .= P_INDEX.'/';
			if ( isset($component) ) {
				// Get rid of 'com_', if it's not the system component.
				$return .= ($component == 'system' ? $component : substr($component, 4)).'/';
				if (isset($action))
					$return .= "$action/";
			}
			if ( $params ) {
				$return .= '?';
				foreach ($params as $key => $value) {
					if ( $param_return )
						$param_return .= '&';
					$param_return .= $key.'='.urlencode($value);
				}
				$return .= $param_return;
			}
		} else {
			$return .= ($pines->config->use_htaccess) ? '?' : P_INDEX.'?';
			if ( isset($component) ) {
				$param_return = 'option='.urlencode($component);
				if (isset($action))
					$param_return .= '&action='.urlencode($action);
			}
			if ( $params ) {
				foreach ($params as $key => $value) {
					if ( $param_return )
						$param_return .= '&';
					$param_return .= $key.'='.urlencode($value);
				}
			}
			$return .= $param_return;
		}
		return $return;
	}
}

?>
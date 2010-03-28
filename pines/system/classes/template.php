<?php
/**
 * template class.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
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
class template extends p_base {
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
	 * @param bool $encode_entities Whether to encode HTML entities, such as the ampersand. Use this if the URL is going to be displayed on an HTML page.
	 * @param bool $full_location Whether to return an absolute URL or a relative URL.
	 * @return string The URL in a format to work with the current configuration of Pines.
	 */
	function url($component = null, $action = null, $params = array(), $encode_entities = true, $full_location = false) {
		global $pines;
		if ( is_null($params) ) $params = array();
		if ( $pines->config->allow_template_override && isset($_REQUEST['template']) )
			$params['template'] = $_REQUEST['template'];
		$return = ($full_location) ? $pines->config->full_location : $pines->config->rela_location;
		if ( is_null($component) && empty($params) )
			return $return;
		if ( $pines->config->url_rewriting ) {
			if ( !$pines->config->use_htaccess )
				$return .= P_INDEX.'/';
			if ( !is_null($component) ) {
				// Get rid of 'com_', if it's not the system component.
				$return .= ($component == 'system' ? $component : substr($component, 4)).'/';
				if (!is_null($action))
					$return .= "$action/";
			}
			if ( !empty($params) ) {
				$return .= '?';
				foreach ($params as $key => $value) {
					if ( !empty($param_return) )
						$param_return .= '&';
					$param_return .= "$key=$value";
				}
				$return .= ($encode_entities) ? htmlentities($param_return) : $param_return;
			}
		} else {
			$return .= ($pines->config->use_htaccess) ? '?' : P_INDEX.'?';
			if ( !is_null($component) ) {
				$param_return = "option=$component";
				if (!is_null($action))
					$param_return .= "&action=$action";
			}
			if ( !empty($params) ) {
				foreach ($params as $key => $value) {
					if ( !empty($param_return) )
						$param_return .= '&';
					$param_return .= "$key=$value";
				}
			}
			$return .= ($encode_entities) ? htmlentities($param_return) : $param_return;
		}
		return $return;
	}
}

?>
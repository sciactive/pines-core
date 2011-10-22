<?php
/**
 * depend class.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Dependency checker.
 *
 * To add a dependency checker type, assign a callback to the $checkers array.
 *
 * <code>
 * $pines->depend->checkers['my_type'] = array($pines->com_mycomponent, 'my_checking_method');
 * </code>
 *
 * Your checker callback should return true if the dependency is satisfied, or
 * false if it is not.
 *
 * @package Pines
 */
class depend {
	/**
	 * An array of dependency checker callbacks.
	 * @var array $checkers
	 */
	public $checkers = array();

	/**
	 * Set up the default dependency checker types.
	 *
	 * - ability (System abilities.)
	 * - action (Current or requested action.)
	 * - class (Class exists.)
	 * - component (Installed enabled components and version.)
	 * - extension (PHP extension version.)
	 * - function (Function exists.)
	 * - host (Server hostname.)
	 * - option (Current or requested component.)
	 * - php (PHP version.)
	 * - pines (Pines version.)
	 * - service (Available services.)
	 */
	public function __construct() {
		global $pines;
		$this->checkers = array(
			'ability' => array($this, 'check_ability'),
			'action' => array($this, 'check_action'),
			'class' => array($this, 'check_class'),
			'clientip' => array($this, 'check_clientip'),
			'component' => array($this, 'check_component'),
			'extension' => array($this, 'check_extension'),
			'function' => array($this, 'check_function'),
			'host' => array($this, 'check_host'),
			'option' => array($this, 'check_option'),
			'php' => array($this, 'check_php'),
			'pines' => array($this, 'check_pines'),
			'service' => array($this, 'check_service')
		);
	}

	/**
	 * Check a dependency using one of the available checker types.
	 *
	 * If the requested checker type is not available, check() will return
	 * false.
	 *
	 * @param string $type The type of dependency to be checked.
	 * @param mixed $value The value to be checked.
	 * @return bool The result of the dependency check.
	 */
	public function check($type, $value) {
		if (!isset($this->checkers[$type]))
			return false;
		return call_user_func($this->checkers[$type], $value);
	}

	/**
	 * Check whether the user has the given ability.
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @access private
	 * @uses gatekeeper()
	 * @param string $value The value to check.
	 * @return bool The result of the ability check.
	 */
	private function check_ability($value) {
		global $pines;
		if ($value == '!')
			return (isset($pines->user_manager) ? !$pines->user_manager->gatekeeper() : false);
		if (
				strpos($value, '&') !== false ||
				strpos($value, '|') !== false ||
				strpos($value, '!') !== false ||
				strpos($value, '(') !== false ||
				strpos($value, ')') !== false
			)
			return $this->simple_parse($value, array($this, 'check_ability'));
		return (isset($pines->user_manager) ? $pines->user_manager->gatekeeper($value) : true);
	}

	/**
	 * Check against the current or requested action.
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @access private
	 * @uses pines::action
	 * @uses pines::request_action
	 * @param string $value The value to check.
	 * @return bool The result of the action check.
	 */
	private function check_action($value) {
		global $pines;
		if (
				strpos($value, '&') !== false ||
				strpos($value, '|') !== false ||
				strpos($value, '!') !== false ||
				strpos($value, '(') !== false ||
				strpos($value, ')') !== false
			)
			return $this->simple_parse($value, array($this, 'check_action'));
		return $pines->request_action == $value || (isset($pines->action) && $pines->action == $value);
	}

	/**
	 * Check to see if a class exists.
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @access private
	 * @param string $value The value to check for.
	 * @return bool The result of the class check.
	 */
	private function check_class($value) {
		global $pines;
		if (
				strpos($value, '&') !== false ||
				strpos($value, '|') !== false ||
				strpos($value, '!') !== false ||
				strpos($value, '(') !== false ||
				strpos($value, ')') !== false
			)
			return $this->simple_parse($value, array($this, 'check_class'));
		return class_exists($value);
	}

	/**
	 * Check the client's IP address.
	 *
	 * The syntax is as follows:
	 *
	 * IP - Only matches one IP address.
	 *
	 * <pre>0.0.0.0</pre>
	 *
	 * CIDR - Matches a network using CIDR notation.
	 *
	 * <pre>0.0.0.0/24</pre>
	 *
	 * Subnet Mask - Matches a network using a subnet mask.
	 *
	 * <pre>0.0.0.0/255.255.255.0</pre>
	 *
	 * IP Range - Matches a range of IP addresses.
	 *
	 * <pre>0.0.0.0-0.0.0.255</pre>
	 *
	 * The string "{server_addr}" (without quotes) will be replaced by the
	 * server's IP address (from $_SERVER['SERVER_ADDR']). Be aware that it may
	 * be IPv6, which will not work with this function.
	 *
	 * Examples
	 *
	 * - 192.168/24 = 192.168.0.0/255.255.255.0 = The request is on the 192.168.0.X network.
	 * - 128.64/16 = 128.64.0.0/255.255.0.0 = The request is on the 128.64.X.X network.
	 * - {server_addr} = The request is coming from localhost.
	 * - {server_addr}/24 = {server_addr}/255.255.255.0 = The client is on the same 255.255.255.0 subnet as the server.
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @access private
	 * @uses pines::check_ip_cidr()
	 * @uses pines::check_ip_subnet()
	 * @uses pines::check_ip_range()
	 * @param string $value The value to check.
	 * @return bool The result of the action check.
	 */
	private function check_clientip($value) {
		global $pines;
		$value = str_replace('{server_addr}', $_SERVER['SERVER_ADDR'], $value);
		if (
				strpos($value, '&') !== false ||
				strpos($value, '|') !== false ||
				strpos($value, '!') !== false ||
				strpos($value, '(') !== false ||
				strpos($value, ')') !== false
			)
			return $this->simple_parse($value, array($this, 'check_clientip'));
		$client_ip = $_SERVER['REMOTE_ADDR'];
		if ($client_ip == $value) {
			// IP
			return true;
		} elseif (preg_match('/^\d{1,3}(\.\d{1,3}){0,3}\/\d{1,2}$/', $value)) {
			// CIDR
			return $pines->check_ip_cidr($client_ip, $value);
		} elseif (preg_match('/^\d{1,3}(\.\d{1,3}){3}\/\d{1,3}(\.\d{1,3}){3}$/', $value)) {
			// Subnet Mask
			$params = explode('/', $value);
			return $pines->check_ip_subnet($client_ip, $params[0], $params[1]);
		} elseif (preg_match('/^\d{1,3}(\.\d{1,3}){3}-\d{1,3}(\.\d{1,3}){3}$/', $value)) {
			// IP Range
			$params = explode('-', $value);
			return $pines->check_ip_range($client_ip, $params[0], $params[1]);
		}
		// Not formatted correctly. May be IPv6 though.
		return false;
	}

	/**
	 * Check if a component is installed and check its version.
	 *
	 * You can either check only that the component is installed, by using its
	 * name, or that the component's version matches a certain version/range.
	 *
	 * Operators should be placed between the component name and the version
	 * number to test. Such as, "com_xmlparser>=1.1.0". The available operators
	 * are:
	 *
	 * - =
	 * - <
	 * - >
	 * - <=
	 * - >=
	 * - <>
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @access private
	 * @uses pines::components
	 * @param string $value The value to check.
	 * @return bool The result of the component check.
	 */
	private function check_component($value) {
		global $pines;
		if (
				strpos($value, '&') !== false ||
				strpos($value, '|') !== false ||
				strpos($value, '!') !== false ||
				strpos($value, '(') !== false ||
				strpos($value, ')') !== false
			)
			return $this->simple_parse($value, array($this, 'check_component'));
		$component = preg_replace('/([a-z0-9_]+)([<>=]{1,2})(.+)/S', '$1', $value);
		if ($component == $value) {
			return in_array($value, $pines->components);
		} else {
			if (!isset($pines->info->$component))
				return false;
			$compare = preg_replace('/([a-z0-9_]+)([<>=]{1,2})(.+)/S', '$2', $value);
			$required = preg_replace(' /([a-z0-9_]+)([<>=]{1,2})(.+)/S', '$3', $value);
			return version_compare($pines->info->$component->version, $required, $compare);
		}
	}

	/**
	 * Check if a PHP extension is installed and check its version.
	 *
	 * You can either check only that the extension is installed, by using its
	 * name, or that the extension's version matches a certain version/range.
	 *
	 * Operators should be placed between the extension name and the version
	 * number to test. Such as, "tidy>=2.0". The available operators are:
	 *
	 * - =
	 * - <
	 * - >
	 * - <=
	 * - >=
	 * - <>
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @access private
	 * @param string $value The value to check.
	 * @return bool The result of the extension check.
	 */
	private function check_extension($value) {
		global $pines;
		if (
				strpos($value, '&') !== false ||
				strpos($value, '|') !== false ||
				strpos($value, '!') !== false ||
				strpos($value, '(') !== false ||
				strpos($value, ')') !== false
			)
			return $this->simple_parse($value, array($this, 'check_extension'));
		$extension = preg_replace('/([^<>=]+)([<>=]{1,2})(.+)/S', '$1', $value);
		if ($extension == $value) {
			return (phpversion($extension) !== false);
		} else {
			if (phpversion($extension) === false)
				return false;
			$compare = preg_replace('/([^<>=]+)([<>=]{1,2})(.+)/S', '$2', $value);
			$required = preg_replace(' /([^<>=]+)([<>=]{1,2})(.+)/S', '$3', $value);
			return version_compare(phpversion($extension), $required, $compare);
		}
	}

	/**
	 * Check to see if a function exists.
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @access private
	 * @param string $value The value to check.
	 * @return bool The result of the function check.
	 */
	private function check_function($value) {
		global $pines;
		if (
				strpos($value, '&') !== false ||
				strpos($value, '|') !== false ||
				strpos($value, '!') !== false ||
				strpos($value, '(') !== false ||
				strpos($value, ')') !== false
			)
			return $this->simple_parse($value, array($this, 'check_function'));
		return function_exists($value);
	}

	/**
	 * Check the hostname of the server.
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @access private
	 * @param string $value The value to check.
	 * @return bool The result of the host check.
	 */
	private function check_host($value) {
		global $pines;
		if (
				strpos($value, '&') !== false ||
				strpos($value, '|') !== false ||
				strpos($value, '!') !== false ||
				strpos($value, '(') !== false ||
				strpos($value, ')') !== false
			)
			return $this->simple_parse($value, array($this, 'check_host'));
		return $value == $_SERVER['SERVER_NAME'];
	}

	/**
	 * Check against the current or requested component.
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @access private
	 * @uses pines::component
	 * @uses pines::request_component
	 * @param string $value The value to check.
	 * @return bool The result of the component check.
	 */
	private function check_option($value) {
		global $pines;
		if (
				strpos($value, '&') !== false ||
				strpos($value, '|') !== false ||
				strpos($value, '!') !== false ||
				strpos($value, '(') !== false ||
				strpos($value, ')') !== false
			)
			return $this->simple_parse($value, array($this, 'check_option'));
		return $pines->request_component == $value || (isset($pines->component) && $pines->component == $value);
	}

	/**
	 * Check PHP's version.
	 *
	 * Operators should be placed before the version number to test. Such as,
	 * ">=5.2.10". The available operators are:
	 *
	 * - =
	 * - <
	 * - >
	 * - <=
	 * - >=
	 * - <>
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @access private
	 * @param string $value The value to check.
	 * @return bool The result of the version comparison.
	 */
	private function check_php($value) {
		global $pines;
		if (
				strpos($value, '&') !== false ||
				strpos($value, '|') !== false ||
				strpos($value, '!') !== false ||
				strpos($value, '(') !== false ||
				strpos($value, ')') !== false
			)
			return $this->simple_parse($value, array($this, 'check_php'));
		// <, >, =, <=, >=
		$compare = preg_replace('/([<>=]{1,2})(.+)/S', '$1', $value);
		$required = preg_replace('/([<>=]{1,2})(.+)/S', '$2', $value);
		return version_compare(phpversion(), $required, $compare);
	}

	/**
	 * Check Pines' version.
	 *
	 * Operators should be placed before the version number to test. Such as,
	 * ">=1.0.0". The available operators are:
	 *
	 * - =
	 * - <
	 * - >
	 * - <=
	 * - >=
	 * - <>
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @access private
	 * @param string $value The value to check.
	 * @return bool The result of the version comparison.
	 */
	private function check_pines($value) {
		global $pines;
		if (
				strpos($value, '&') !== false ||
				strpos($value, '|') !== false ||
				strpos($value, '!') !== false ||
				strpos($value, '(') !== false ||
				strpos($value, ')') !== false
			)
			return $this->simple_parse($value, array($this, 'check_pines'));
		// <, >, =, <=, >=
		$compare = preg_replace('/([<>=]{1,2})(.+)/S', '$1', $value);
		$required = preg_replace('/([<>=]{1,2})(.+)/S', '$2', $value);
		return version_compare($pines->info->version, $required, $compare);
	}

	// Is this safe? Consider that users can use dependencies to discover things...
	//private function check_server($value) {
		// {server_addr} $_SERVER['SERVER_ADDR'] Server IP address.
		// {server_name} $_SERVER['SERVER_NAME'] Server hostname.
		// {server_software} $_SERVER['SERVER_SOFTWARE'] Server identification string.
		// {server_protocol} $_SERVER['SERVER_PROTOCOL'] Name and revision of the information protocol via which the page was requested; i.e. 'HTTP/1.0'.
		// {request_method} $_SERVER['REQUEST_METHOD'] Which request method was used to access the page; i.e. 'GET', 'HEAD', 'POST', 'PUT'.
		// {request_time} $_SERVER['REQUEST_TIME'] The timestamp of the start of the request.
		// {http_accept} $_SERVER['HTTP_ACCEPT'] Contents of the Accept: header from the current request, if there is one.
		// {http_accept_charset} $_SERVER['HTTP_ACCEPT_CHARSET'] Contents of the Accept-Charset: header from the current request, if there is one. Example: 'iso-8859-1,*,utf-8'.
		// {http_accept_encoding} $_SERVER['HTTP_ACCEPT_ENCODING'] Contents of the Accept-Encoding: header from the current request, if there is one. Example: 'gzip'.
		// {http_accept_language} $_SERVER['HTTP_ACCEPT_LANGUAGE'] Contents of the Accept-Language: header from the current request, if there is one. Example: 'en'.
		// {http_connection} $_SERVER['HTTP_CONNECTION'] Contents of the Connection: header from the current request, if there is one. Example: 'Keep-Alive'.
		// {http_host} $_SERVER['HTTP_HOST'] Contents of the Host: header from the current request, if there is one.
		// {http_referer} $_SERVER['HTTP_REFERER'] The address of the page (if any) which referred the user agent to the current page. This is set by the user agent.
		// {http_user_agent} $_SERVER['HTTP_USER_AGENT'] Contents of the User-Agent: header from the current request, if there is one. This is a string denoting the user agent being which is accessing the page.
		// {https} $_SERVER['HTTPS'] Set to a non-empty value if the script was queried through the HTTPS protocol.
		// {remote_addr} $_SERVER['REMOTE_ADDR'] The IP address from which the user is viewing the current page.
		// {remote_host} $_SERVER['REMOTE_HOST'] The Host name from which the user is viewing the current page. The reverse dns lookup is based off the REMOTE_ADDR of the user.
		// {remote_port} $_SERVER['REMOTE_PORT'] The port being used on the user's machine to communicate with the web server.
	//}

	/**
	 * Check if a service is available.
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @access private
	 * @uses pines::services
	 * @param string $value The value to check.
	 * @return bool The result of the service check.
	 */
	private function check_service($value) {
		global $pines;
		if (
				strpos($value, '&') !== false ||
				strpos($value, '|') !== false ||
				strpos($value, '!') !== false ||
				strpos($value, '(') !== false ||
				strpos($value, ')') !== false
			)
			return $this->simple_parse($value, array($this, 'check_service'));
		return key_exists($value, $pines->services);
	}

	/**
	 * Parse simple logic statements using a callback.
	 *
	 * Logic statements can be made with the following operators:
	 * - ! (Bang - Not)
	 * - & (Ampersand - And)
	 * - | (Pipe - Or)
	 *
	 * They can be grouped using parentheses.
	 *
	 * For example:
	 * <code>
	 * simple_parse('!val1&(val2|!val3|(val2&!val4))', array($pines->com_mycomponent, 'my_checking_method'));
	 * </code>
	 *
	 * @param string $value The logic statement.
	 * @param callback $callback The callback with which to check each part.
	 * @return bool The result of the parsing.
	 */
	public function simple_parse($value, $callback) {
		// ex: !val1&(val2|!val3|(val2&val4))
		// Check whether there are parts, and fill an array with them.
		$words = preg_split('/([!&|()])/S', $value, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		if (count($words) == 1)
			return call_user_func($callback, $value);

		// For every match, replace it with a call to the callback.
		$parsable = '';
		foreach ($words as $cur_word) {
			switch ($cur_word) {
				case '!':
				case '(':
				case ')':
					$parsable .= $cur_word;
					break;
				case '&':
					$parsable .= '&&';
					break;
				case '|':
					$parsable .= '||';
					break;
				default:
					// Let PHP call the callback, so it knows when it can stop.
					$parsable .= 'call_user_func($callback, \''.addslashes($cur_word).'\')';
					break;
			}
		}

		// Use PHP to evaluate the string.
		return eval('return ('.$parsable.');');
	}
}

?>
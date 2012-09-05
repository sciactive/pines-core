<?php
/**
 * depend class.
 *
 * @package Core
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
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
 * false if it is not. It should also provide a help array if the help argument
 * is true.
 *
 * @package Core
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
	 * - request (Requested component + action.)
	 * - service (Available services.)
	 */
	public function __construct() {
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
			'request' => array($this, 'check_request'),
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
	 * Get a dependency checker's help documentation.
	 * 
	 * The help array is an associative array with the following values:
	 * 
	 * - cname - A common name (title) for the checker.
	 * - description - A markdown formatted description of the checker.
	 * - syntax - A markdown formatted guide for the syntax of the checker.
	 * - simple_parse - A true/false; whether the checker uses simple_parse to
	 *   provide simple logic processing.
	 *
	 * @param string $type The type of dependency to get help for.
	 * @return array|null The help array, or null if the checker doesn't provide one.
	 */
	public function help($type) {
		if (!isset($this->checkers[$type]))
			return array();
		$return = call_user_func($this->checkers[$type], '', true);
		if ((array) $return === $return)
			return $return;
		return null;
	}

	/**
	 * Check whether the user has the given ability.
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @access private
	 * @uses gatekeeper()
	 * @param string $value The value to check.
	 * @param bool $help Whether to return the help for this checker.
	 * @return bool|array The result of the check, or the help array.
	 */
	private function check_ability($value, $help = false) {
		global $pines;
		if ($help) {
			$return = array();
			$return['cname'] = 'Ability Checker';
			$return['description'] = <<<'EOF'
Check against the current user's abilities.

An ability begins with the name of the component, then a slash and the name of
the ability, such as com_example/editfoobar. You can find a list of all
abilities on the new user form under the Abilities tab. Hover over an ability's
label to see the name of the ability.
EOF;
			$return['syntax'] = <<<'EOF'
Providing the name of the ability will check whether the user has the ability.
Put an exclamation point before the ability to check if they don't have the
ability. Leave blank to check that the user is logged in, or put only an
exclamation point to check that the user is not logged in.

com_user/login&com_example/editfoobar
:	Check that the user has both abilities.

com_user/login&!com_user/edituser
:	Check that the user has the login ability, but not the edituser ability.

com_user/login&(com_user/edituser|com_user/editgroup)
:	Check that the user has the login ability, and either the edituser ability
	or the editgroup ability.
EOF;
			$return['simple_parse'] = true;
			return $return;
		}
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
	 * If value is only a '!', then only the requested action is checked. This
	 * is because action will never be empty.
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @access private
	 * @uses pines::action
	 * @uses pines::request_action
	 * @param string $value The value to check.
	 * @param bool $help Whether to return the help for this checker.
	 * @return bool|array The result of the check, or the help array.
	 */
	private function check_action($value, $help = false) {
		global $pines;
		if ($help) {
			$return = array();
			$return['cname'] = 'Action Checker';
			$return['description'] = <<<'EOF'
Check against the current or requested action.

The requested action is what appears in the URL following "action=". If you have
URL rewriting turned on, it will be the portion of the URL following the
component (option) portion. This check will check both the requested action
and/or the currently running action.

Note that "%2F" in an action in the URL should be replaced with a forward
slash (/) here.
EOF;
			$return['syntax'] = <<<'EOF'
Providing the name of the action will check whether the requested action or the
current action matches. Put an exclamation point before the action to check if
neither match. Leave blank to check that an action was requested, or put only an
exclamation point to check that no action was requested. Put a caret (^) before
the action to only check the requested action, and put a greater-than sign (>)
before it to only check the current action.

\!
:	Check that no action was requested.

sale/edit|return/edit
:	Check that either the sale/edit action or the return/edit action was
	requested or is currently running.

^sale/edit
:	Check that the sale/edit action was requested.

>recoverpassword
:	Check that the recoverpassword action is currently running.
EOF;
			$return['simple_parse'] = true;
			return $return;
		}
		if ($value == '!')
			return (!empty($pines->request_action));
		elseif ($value == '')
			return (empty($pines->request_action));
		if (
				strpos($value, '&') !== false ||
				strpos($value, '|') !== false ||
				strpos($value, '!') !== false ||
				strpos($value, '(') !== false ||
				strpos($value, ')') !== false
			)
			return $this->simple_parse($value, array($this, 'check_action'));
		if (substr($value, 0, 1) == '^')
			return ($pines->request_action == substr($value, 1));
		elseif (substr($value, 0, 1) == '>')
			return ($pines->action == substr($value, 1));
		return $pines->request_action == $value || $pines->action == $value;
	}

	/**
	 * Check to see if a class exists.
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @access private
	 * @param string $value The value to check for.
	 * @param bool $help Whether to return the help for this checker.
	 * @return bool|array The result of the check, or the help array.
	 */
	private function check_class($value, $help = false) {
		if ($help) {
			$return = array();
			$return['cname'] = 'Class Checker';
			$return['description'] = <<<'EOF'
Check that a class exists using the PHP class_exists function.
EOF;
			$return['syntax'] = <<<'EOF'
Providing the name of the class will check whether the class exists. Put an
exclamation point before the name to check if the class doesn't exist.

Imagick
:	Check that the ImageMagick class "Imagick" exists.

!Phar
:	Check that the Phar class does not exist.
EOF;
			$return['simple_parse'] = true;
			return $return;
		}
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
	 * - 192.168/24 = 192.168.0.0/255.255.255.0 = The client is on the 192.168.0.X network.
	 * - 128.64/16 = 128.64.0.0/255.255.0.0 = The client is on the 128.64.X.X network.
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
	 * @param bool $help Whether to return the help for this checker.
	 * @return bool|array The result of the check, or the help array.
	 */
	private function check_clientip($value, $help = false) {
		global $pines;
		if ($help) {
			$return = array();
			$return['cname'] = 'Client IP Checker';
			$return['description'] = <<<'EOF'
Check the client's IP address.

Check that the client's IP address (or the IP address of the proxy they are
connecting with) matches a given IP or range of IPs.
EOF;
			$return['syntax'] = <<<'EOF'
Providing an IP or IP range/network will check that the client's IP matches. Put
an exclamation point before it to negate the check. There are four different
syntaxes available:

*	IP - Only matches one IP address.
	
	<pre>0.0.0.0</pre>
*	CIDR - Matches a network using CIDR notation.
	
	<pre>0.0.0.0/24</pre>
*	Subnet Mask - Matches a network using a subnet mask.
	
	<pre>0.0.0.0/255.255.255.0</pre>
*	IP Range - Matches a range of IP addresses.
	
	<pre>0.0.0.0-0.0.0.255</pre>

The string "{server_addr}" (without quotes) will be replaced by the server's IP
address (from $_SERVER['SERVER_ADDR']). Be aware that it may be IPv6, which will
not work with this checker.

192.168/24
192.168.0.0/255.255.255.0
:	The client is on the 192.168.0.X network.

128.64/16
128.64.0.0/255.255.0.0
:	The client is on the 128.64.X.X network.

{server_addr}
:	The request is coming from localhost.

{server_addr}/24
{server_addr}/255.255.255.0
:	The client is on the same 255.255.255.0 subnet as the server.
EOF;
			$return['simple_parse'] = true;
			return $return;
		}
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
	 * You can either check only that the component is installed by using its
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
	 * @param bool $help Whether to return the help for this checker.
	 * @return bool|array The result of the check, or the help array.
	 */
	private function check_component($value, $help = false) {
		global $pines;
		if ($help) {
			$return = array();
			$return['cname'] = 'Component Checker';
			$return['description'] = <<<'EOF'
Check if a component is installed and check its version.
EOF;
			$return['syntax'] = <<<'EOF'
You can either check only that the component is installed (and enabled) by using
its name, or that the component's version matches a certain version/range.

Operators should be placed between the component name and the version number to
test. Such as, "com_xmlparser>=1.1.0". The available operators are:

* `=`
* `<`
* `>`
* `<=`
* `>=`
* `<>`

com_user
:	Check that com_user is installed and enabled.

com_sales>=1.0.1
:	Check that com_sales is installed and enabled, and that it is at least
	version 1.0.1.

com_customer<>1.0.0|(com_customer=1.0.0&com_sales)
:	Check that either a version of com_customer other than 1.0.0 is installed
	and enabled, or that com_customer version 1.0.0 and com_sales are both
	installed and enabled.
EOF;
			$return['simple_parse'] = true;
			return $return;
		}
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
	 * You can either check only that the extension is installed by using its
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
	 * @param bool $help Whether to return the help for this checker.
	 * @return bool|array The result of the check, or the help array.
	 */
	private function check_extension($value, $help = false) {
		if ($help) {
			$return = array();
			$return['cname'] = 'Extension Checker';
			$return['description'] = <<<'EOF'
Check if a PHP extension is installed and check its version.
EOF;
			$return['syntax'] = <<<'EOF'
You can either check only that the extension is installed by using its name, or
that the extension's version matches a certain version/range.

Operators should be placed between the extension name and the version number to
test. Such as, "tidy>=2.0". The available operators are:

* `=`
* `<`
* `>`
* `<=`
* `>=`
* `<>`

xdebug
:	Check that Xdebug is installed.

curl>=7.0.0
:	Check that cURL is installed, and that it is at least version 7.

imagick>3.0|gd>=2
:	Check that either ImageMagick greater than version 3 is installed, or GD
	version 2 or greater is installed.
EOF;
			$return['simple_parse'] = true;
			return $return;
		}
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
	 * @param bool $help Whether to return the help for this checker.
	 * @return bool|array The result of the check, or the help array.
	 */
	private function check_function($value, $help = false) {
		if ($help) {
			$return = array();
			$return['cname'] = 'Function Checker';
			$return['description'] = <<<'EOF'
Check that a function exists using the PHP function_exists function.
EOF;
			$return['syntax'] = <<<'EOF'
Providing the name of the function will check whether the function exists. Put
an exclamation point before the name to check if the function doesn't exist.

imagecreate
:	Check that the Graphics Draw function "imagecreate" exists.

!gmp_init
:	Check that the GMP function "gmp_init" does not exist.
EOF;
			$return['simple_parse'] = true;
			return $return;
		}
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
	 * @param bool $help Whether to return the help for this checker.
	 * @return bool|array The result of the check, or the help array.
	 */
	private function check_host($value, $help = false) {
		if ($help) {
			$return = array();
			$return['cname'] = 'Hostname Checker';
			$return['description'] = <<<'EOF'
Check the hostname of the server.
EOF;
			$return['syntax'] = <<<'EOF'
When you use Pines to host multiple websites, you can use this checker to
determine which website is being requested. If Pines is running on a virtual
host, this will check the value defined for that virtual host.

sciactive.com|www.sciactive.com
:	Check that the client is requesting sciactive.com (or www.sciactive.com).

!(bigbobsvalueshack.com|www.bigbobsvalueshack.com)
!bigbobsvalueshack.com&!www.bigbobsvalueshack.com
:	Check that the client is not requesting bigbobsvalueshack.com.
EOF;
			$return['simple_parse'] = true;
			return $return;
		}
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
	 * If value is only a '!', then only the requested component is checked.
	 * This is because component will never be empty.
	 * 
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @access private
	 * @uses pines::component
	 * @uses pines::request_component
	 * @param string $value The value to check.
	 * @param bool $help Whether to return the help for this checker.
	 * @return bool|array The result of the check, or the help array.
	 */
	private function check_option($value, $help = false) {
		global $pines;
		if ($help) {
			$return = array();
			$return['cname'] = 'Component (Option) Checker';
			$return['description'] = <<<'EOF'
Check against the current or requested component.

The requested component is what appears in the URL following "option=". If you
have URL rewriting turned on, it will be the portion of the URL following the
Pines location, but will be missing the "com_". This check will check both the
requested component and/or the currently running action's component.
EOF;
			$return['syntax'] = <<<'EOF'
Providing the name of the component will check whether the requested component
or the current component matches. Put an exclamation point before the component
to check if neither match. Leave blank to check that a component was requested,
or put only an exclamation point to check that no component was requested. Put a
caret (^) before the component to only check the requested component, and put a
greater-than sign (>) before it to only check the current component.

\!
:	Check that no component was requested.

com_sales|com_storefront
:	Check that either a com_sales action or a com_storefront action was
	requested or is currently running.

^com_sales
:	Check that a com_sales action was requested.

>com_user
:	Check that a com_user action is currently running.
EOF;
			$return['simple_parse'] = true;
			return $return;
		}
		if ($value == '!')
			return (!empty($pines->request_component));
		elseif ($value == '')
			return (empty($pines->request_component));
		if (
				strpos($value, '&') !== false ||
				strpos($value, '|') !== false ||
				strpos($value, '!') !== false ||
				strpos($value, '(') !== false ||
				strpos($value, ')') !== false
			)
			return $this->simple_parse($value, array($this, 'check_option'));
		if (substr($value, 0, 1) == '^')
			return ($pines->request_component == substr($value, 1));
		elseif (substr($value, 0, 1) == '>')
			return ($pines->component == substr($value, 1));
		return $pines->request_component == $value || $pines->component == $value;
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
	 * @param bool $help Whether to return the help for this checker.
	 * @return bool|array The result of the check, or the help array.
	 */
	private function check_php($value, $help = false) {
		if ($help) {
			$return = array();
			$return['cname'] = 'PHP Version Checker';
			$return['description'] = <<<'EOF'
Check the version of PHP running.
EOF;
			$return['syntax'] = <<<'EOF'
Operators should be placed before the version number to test. Such as,
">=5.2.10". The available operators are:

* `=`
* `<`
* `>`
* `<=`
* `>=`
* `<>`

>=5
:	Check that PHP is at least version 5.

<5.4
:	Check that PHP is less than version 5.4.
EOF;
			$return['simple_parse'] = true;
			return $return;
		}
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
	 * @param bool $help Whether to return the help for this checker.
	 * @return bool|array The result of the check, or the help array.
	 */
	private function check_pines($value, $help = false) {
		global $pines;
		if ($help) {
			$return = array();
			$return['cname'] = 'Pines Version Checker';
			$return['description'] = <<<'EOF'
Check the version of Pines running.
EOF;
			$return['syntax'] = <<<'EOF'
Operators should be placed before the version number to test. Such as,
">=1.0.0". The available operators are:

* `=`
* `<`
* `>`
* `<=`
* `>=`
* `<>`

>=1.0.0
:	Check that Pines is at least version 1.0.0.

<2
:	Check that Pines is less than version 2.
EOF;
			$return['simple_parse'] = true;
			return $return;
		}
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

	/**
	 * Check against the requested component and action.
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @access private
	 * @uses pines::request_component
	 * @uses pines::request_action
	 * @param string $value The value to check.
	 * @param bool $help Whether to return the help for this checker.
	 * @return bool|array The result of the check, or the help array.
	 */
	private function check_request($value, $help = false) {
		global $pines;
		if ($help) {
			$return = array();
			$return['cname'] = 'Request Component/Action Checker';
			$return['description'] = <<<'EOF'
Check against the requested component and action.

See the descriptions of the component (option) and action checkers for
information on how to decipher URLs.
EOF;
			$return['syntax'] = <<<'EOF'
Providing the name of the component/action will check whether the requested
component and action match. Put an exclamation point before the value to check
if they don't match. Leave blank to check that a component and action were
requested (in this case, a component request with no action passes), or put only
an exclamation point to check that no component and action were requested.

\!
:	Check that no component and action were requested. This is how you check
	that the user is on the homepage.

com_storefront/category/browse|com_storefront/product
:	Check that the user requested either a category page or a product page in
	the storefront component.

!com_user/recover
:	Check that the user did not request the account recovery page.
EOF;
			$return['simple_parse'] = true;
			return $return;
		}
		if ($value == '!')
			return (!empty($pines->request_component) || !empty($pines->request_action));
		if (
				strpos($value, '&') !== false ||
				strpos($value, '|') !== false ||
				strpos($value, '!') !== false ||
				strpos($value, '(') !== false ||
				strpos($value, ')') !== false
			)
			return $this->simple_parse($value, array($this, 'check_action'));
		return $pines->request_component == $value || ("{$pines->request_component}/{$pines->request_action}" == $value);
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
	 * Check if a service is installed and enabled.
	 *
	 * Uses simple_parse() to provide simple logic.
	 *
	 * @access private
	 * @uses pines::services
	 * @param string $value The value to check.
	 * @param bool $help Whether to return the help for this checker.
	 * @return bool|array The result of the check, or the help array.
	 */
	private function check_service($value, $help = false) {
		global $pines;
		if ($help) {
			$return = array();
			$return['cname'] = 'Service Checker';
			$return['description'] = <<<'EOF'
Check if a service is installed and enabled.
EOF;
			$return['syntax'] = <<<'EOF'
Provide the name of a service to check that it is installed and enabled.

editor
:	Check that an editor is installed and enabled.

entity_manager&user_manager
:	Check that an entity manager and a user manager are both installed and
	enabled.
EOF;
			$return['simple_parse'] = true;
			return $return;
		}
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
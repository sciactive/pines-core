<?php
/**
 * pines class.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A dynamic component loading class. The class for the $pines object.
 *
 * Component classes will be automatically loaded into their variables. In other
 * words, when you call $pines->com_xmlparser->parse(), if $pines->com_xmlparser
 * is empty, the com_xmlparser class will attempt to be loaded into it. It will
 * then be hooked by the hook manager.
 *
 * @package Pines
 */
class pines {
	/**
	 * Pines' and components' info.
	 * @var info
	 */
	public $info;
	/**
	 * Pines' and components' configuration.
	 * @var config
	 */
	public $config;
	/**
	 * The hook system.
	 * @var hook
	 */
	public $hook;
	/**
	 * The dependency system.
	 * @var depend
	 */
	public $depend;
	/**
	 * The menu system.
	 * @var menu
	 */
	public $menu;
	/**
	 * The display controller.
	 * @var page
	 */
	public $page;
	/**
	 * An array of the enabled components.
	 * @var array
	 */
	public $components = array();
	/**
	 * An array of all components.
	 * @var array
	 */
	public $all_components = array();
	/**
	 * An array of the possible system services.
	 * @var array
	 */
	public $service_names = array('template', 'configurator', 'log_manager', 'entity_manager', 'user_manager', 'editor', 'uploader', 'icons');
	/**
	 * An array of the system services.
	 * @var array
	 */
	public $services = array();
	/**
	 * The name of the current template.
	 * @var string
	 */
	public $current_template;
	/**
	 * List of class files for autoloading classes.
	 *
	 * Note that templates have a classes dir, but the only file loaded from it is
	 * the file of the same name as the template. Also, only the current template's
	 * class is loaded.
	 *
	 * @var array
	 */
	public $class_files = array();
	/**
	 * The requested component/option.
	 * @var string
	 */
	public $request_component;
	/**
	 * The requested action.
	 * @var string
	 */
	public $request_action;
	/**
	 * The currently running component/option.
	 * @var string
	 */
	public $component;
	/**
	 * The currently running action.
	 * @var string
	 */
	public $action;

	/**
	 * Set up the Pines object.
	 */
	public function __construct() {
		if (P_SCRIPT_TIMING) pines_print_time('Load the Pines base system services.');
		$this->config = new config;
		$this->info = new info;
		$this->hook = new hook;
		$this->depend = new depend;
		$this->menu = new menu;
		$this->page = new page;
		if (P_SCRIPT_TIMING) pines_print_time('Load the Pines base system services.');

		$this->load_system_config();

		if (P_SCRIPT_TIMING) pines_print_time('Find Component Classes');
		// Fill the lists of components.
		if ( file_exists('components/') && file_exists('templates/') ) {
			$this->components = array();
			$this->all_components = array_merge(pines_scandir('components/', 0, null, false), pines_scandir('templates/', 0, null, false));
			foreach ($this->all_components as &$cur_value) {
				if (substr($cur_value, 0, 1) == '.') {
					$cur_value = substr($cur_value, 1);
				} else {
					$this->components[] = $cur_value;
				}
			}
			sort($this->components);
			sort($this->all_components);
		}

		// Fill the list of component classes.
		$temp_classes = glob('components/com_*/classes/*.php');
		foreach ($temp_classes as $cur_class) {
			$cur_name = strrchr($cur_class, '/');
			$cur_name = substr($cur_name, 1, strlen($cur_name) -5);
			$this->class_files[$cur_name] = $cur_class;
		}
		if (P_SCRIPT_TIMING) pines_print_time('Find Component Classes');
		
		if (P_SCRIPT_TIMING) pines_print_time('Get Requested Action');
		// Load any post or get vars for our component/action.
		$this->request_component = str_replace('..', 'fail-danger-dont-use-hack-attempt', $_REQUEST['option']);
		$this->request_action = str_replace('..', 'fail-danger-dont-use-hack-attempt', $_REQUEST['action']);

		// URL Rewriting Engine (Simple, eh?)
		// The values from URL rewriting override any post or get vars, so don't submit
		// forms to a url you shouldn't.
		// /index.php/user/group/edit/id-35/ -> /index.php?option=com_user&action=group/edit&id=35
		if ( $this->config->url_rewriting ) {
			$request_string = $_SERVER['REQUEST_URI'];
			// If there's a query part, remove it.
			if (strlen($_SERVER['QUERY_STRING']))
				$request_string = substr($request_string, 0, (strlen($_SERVER['QUERY_STRING']) * -1) - 1);
			// Remove the path to Pines.
			$request_string = substr($request_string, strlen($this->config->rela_location));
			// Get rid of index.php/ at the beginning.
			if (strpos($request_string, P_INDEX.'/') === 0)
				$request_string = substr($request_string, strlen(P_INDEX)+1);
			// And / at the end.
			if (substr($request_string, -1) == '/')
				$request_string = substr($request_string, 0, -1);
			// Get an array of the pseudo directories from the URI.
			$args_array = explode('/', $request_string);
			if ( !empty($args_array[0]) && strpos($args_array[0], '-') === false ) $this->request_component = ($args_array[0] == 'system' ? $args_array[0] : 'com_'.$args_array[0]);
			if ( !empty($args_array[1]) && strpos($args_array[1], '-') === false ) $this->request_action = $args_array[1];
			$arg_count = count($args_array);
			// Check for subdir actions. Note that they can't have dashes.
			for ($i = 2; $i < $arg_count; $i++) {
				if (strpos($args_array[$i], '-') !== false)
					break;
				$this->request_action .= "/{$args_array[$i]}";
			}
			// Any other args are parsed as query data.
			if ($i < $arg_count) {
				for (; $i < $arg_count; $i++) {
					list ($key, $value) = explode('-', $args_array[$i], 2);
					$_GET[$key] = $value;
					$_REQUEST[$key] = $value;
				}
			}
		}
		if (P_SCRIPT_TIMING) pines_print_time('Get Requested Action');
	}

	/**
	 * Retrieve a variable.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 * 
	 * This function will try to load a component's class into any variables
	 * beginning with com_. Standard variables will be loaded into their correct
	 * variables as well.
	 *
	 * @param string $name The name of the variable.
	 * @return mixed The value of the variable or nothing if it doesn't exist.
	 */
	public function &__get($name) {
		if (substr($name, 0, 4) == 'com_') {
			global $pines;
			try {
				$this->$name = new $name;
				$pines->hook->hook_object($this->$name, "\$pines->{$name}->");
				return $this->$name;
			} catch (Exception $e) {
				return;
			}
		}
		if (in_array($name, $this->service_names) && isset($this->services[$name])) {
			global $pines;
			$this->$name = new $this->services[$name];
			$pines->hook->hook_object($this->$name, "\$pines->{$name}->");
			return $this->$name;
		}
	}

	/**
	 * Checks whether a variable is set.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 *
	 * This functions checks whether a class can be loaded for class variables.
	 *
	 * @param string $name The name of the variable.
	 * @return bool
	 */
	public function __isset($name) {
		global $pines;
		if (substr($name, 0, 4) == 'com_')
			return (class_exists($name) || ((array) $pines->class_files === $pines->class_files && isset($pines->class_files[$name])));
		return (in_array($name, $this->service_names) && isset($this->services[$name]));
	}

	/**
	 * Sets a variable.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 * 
	 * This function catches any standard system classes, so they don't get set
	 * to the name of their class. This allows them to be dynamically loaded
	 * when they are first called.
	 *
	 * @param string $name The name of the variable.
	 * @param string $value The value of the variable.
	 * @return mixed The value of the variable.
	 */
	public function __set($name, $value) {
		if (in_array($name, $this->service_names) && is_string($value)) {
			return $this->services[$name] = $value;
		} else {
			return $this->$name = $value;
		}
	}

	/**
	 * Load and run an action.
	 *
	 * If no component/action is specified, the default component is loaded.
	 *
	 * @param string $component The component in which the action resides.
	 * @param string $action The action to run.
	 * @return mixed The value returned by the action, or 'error_404' if it doesn't exist.
	 */
	public function action($component = null, $action = null) {
		global $pines;
		// Fill in any empty vars.
		if ( empty($component) ) $component = $this->config->default_component;
		if ( empty($action) ) $action = 'default';
		$component = str_replace('..', 'fail-danger-dont-use-hack-attempt', $component);
		$action = str_replace('..', 'fail-danger-dont-use-hack-attempt', $action);
		$action_file = ($component == 'system' ? $component : "components/$component")."/actions/$action.php";
		if ( file_exists($action_file) ) {
			$this->component = $component;
			$this->action = $action;
			unset($component);
			unset($action);
			/**
			 * Run the action's file.
			 */
			return require($action_file);
		} else {
			return 'error_404';
		}
	}

	/**
	 * Check if an IP address is on a network using CIDR notation.
	 *
	 * You can shorten the notation by dropping trailing ".0"s.
	 *
	 * @param string $ip The IP address to check.
	 * @param string $cidr The network in CIDR notation. (E.g. 192.168.0.0/24)
	 * @return bool True or false.
	 */
	public function check_ip_cidr($ip, $cidr) {
		// Separate the CIDR notation.
		$ip_arr = explode('/', $cidr);
		// Fill in any missing ".0" parts, and turn the address into a long.
		$cidr_long = ip2long($ip_arr[0].str_repeat('.0', 3 - substr_count($ip_arr[0], '.')));
		$cidr_bits = (int) $ip_arr[1];
		// Turn the IP into a long.
		$ip_long = ip2long($ip);

		// Get the network part of the CIDR and the IP.
		$cidr_network = $cidr_long >> (32 - $cidr_bits);
		$ip_network = $ip_long >> (32 - $cidr_bits);

		// If the network parts are equal, return true.
		return ($cidr_network === $ip_network);
	}

	/**
	 * Check if an IP address is on a network using a subnet mask.
	 *
	 * @param string $ip The IP address to check.
	 * @param string $network The IP address of the network. (Or any address on the network.)
	 * @param string $netmask The subnet mask.
	 * @return bool True or false.
	 */
	public function check_ip_subnet($ip, $network, $netmask) {
		// Turn the addresses into long format.
		$network_long = ip2long($network);
		$mask_long = ip2long($netmask);
		$ip_long = ip2long($ip);

		// Remove the host part of the addresses.
		$network_net_long = $network_long & $mask_long;
		$ip_net_long = $ip_long & $mask_long;

		// If the network parts are equal, return true.
		return ($network_net_long === $ip_net_long);
	}

	/**
	 * Check if an IP address falls within a range of IP addresses.
	 *
	 * @param string $ip The IP address to check.
	 * @param string $from_ip The first IP address of the range.
	 * @param string $to_ip The last IP address of the range.
	 * @return bool True or false.
	 */
	public function check_ip_range($ip, $from_ip, $to_ip) {
		// Turn the addresses into long format.
		$from_ip_long = ip2long($from_ip);
		$to_ip_long = ip2long($to_ip);
		$ip_long = ip2long($ip);

		// If the IP is between the two addresses, return true.
		return ($ip_long >= $from_ip_long && $ip_long <= $to_ip_long);
	}

	/**
	 * Format content to display to the user.
	 *
	 * This function only exists to provide hooking functionality for components
	 * that alter content before displaying to the user.
	 *
	 * @param string $content The content to format.
	 * @return string The formatted content.
	 */
	public function format_content($content) {
		return $content;
	}

	/**
	 * Formats a date using the DateTime class.
	 * 
	 * $type can be any of the following:
	 * 
	 * - full_sort - Date and time, big endian and 24 hour format so it is sortable.
	 * - full_long - Date and time, long format.
	 * - full_med - Date and time, medium format.
	 * - full_short - Date and time, short format.
	 * - date_sort - Only the date, big endian so it is sortable.
	 * - date_long - Only the date, long format.
	 * - date_med - Only the date, medium format.
	 * - date_short - Only the date, short format.
	 * - time_sort - Only the time, 24 hour format so it is sortable.
	 * - time_long - Only the time, long format.
	 * - time_med - Only the time, medium format.
	 * - time_short - Only the time, short format.
	 * - custom - Use whatever is passed in $format.
	 *
	 * A component can hook this function and redirect the call to its own
	 * function in order to provide localization.
	 *
	 * @param int $timestamp The timestamp to format.
	 * @param string $type The type of formatting to use.
	 * @param string $format The format to use if type is 'custom'.
	 * @param DateTimeZone|string|null $timezone The timezone to use for formatting. Defaults to date_default_timezone_get().
	 * @return string The formatted date.
	 */
	public function format_date($timestamp, $type = 'full_sort', $format = '', $timezone = null) {
		// Determine the format to use.
		switch ($type) {
			case 'date_sort':
				$format = 'Y-m-d';
				break;
			case 'date_long':
				$format = 'l, F j, Y';
				break;
			case 'date_med':
				$format = 'j M Y';
				break;
			case 'date_short':
				$format = 'n/d/Y';
				break;
			case 'time_sort':
				$format = 'H:i T';
				break;
			case 'time_long':
				$format = 'g:i:s A T';
				break;
			case 'time_med':
				$format = 'g:i:s A';
				break;
			case 'time_short':
				$format = 'g:i A';
				break;
			case 'full_sort':
				$format = 'Y-m-d H:i T';
				break;
			case 'full_long':
				$format = 'l, F j, Y g:i A T';
				break;
			case 'full_med':
				$format = 'j M Y g:i A T';
				break;
			case 'full_short':
				$format = 'n/d/Y g:i A T';
				break;
			case 'custom':
			default:
				break;
		}
		// Create a date object from the timestamp.
		$date = new DateTime(gmdate('c', (int) $timestamp));
		if (isset($timezone)) {
			if ((object) $timezone !== $timezone)
				$timezone = new DateTimeZone($timezone);
			$date->setTimezone($timezone);
		} else {
			$date->setTimezone(new DateTimeZone(date_default_timezone_get()));
		}
		return $date->format($format);
	}

	/**
	 * Formats a phone number.
	 *
	 * Uses US phone number format. E.g. "(800) 555-1234 x56".
	 *
	 * A component can hook this function and redirect the call to its own
	 * function in order to provide localization.
	 *
	 * @param string $number The phone number to format.
	 * @return string The formatted phone number.
	 */
	public function format_phone($number) {
		if (!isset($number))
			return '';
		$return = preg_replace('/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/', '($1$2$3) $4$5$6-$7$8$9$10 x$11', (string) $number);
		return preg_replace('/\D*$/', '', $return);
	}

	/**
	 * Load the system configuration settings.
	 */
	public function load_system_config() {
		if (P_SCRIPT_TIMING) pines_print_time('Load System Config');
		$this->current_template = ( !empty($_REQUEST['template']) && $this->config->template_override ) ?
			str_replace('..', 'fail-danger-dont-use-hack-attempt', $_REQUEST['template']) : $this->config->default_template;
		$this->template = $this->current_template;
		date_default_timezone_set($this->config->timezone);

		// Check the offline mode, and load the offline page if enabled.
		if ($this->config->offline_mode)
			require('system/offline.php');

		// If the current template is missing its class, display the template error page.
		$template_class_file = "templates/{$this->current_template}/classes/{$this->current_template}.php";
		if ( !file_exists($template_class_file) )
			require('system/template_error.php');
		$this->class_files[$this->current_template] = $template_class_file;
		if (P_SCRIPT_TIMING) pines_print_time('Load System Config');
	}

	/**
	 * Safely redirect to a new URL.
	 *
	 * Redirect the user to a new URL, while still displaying any pending
	 * notices and errors. Keep in mind that notices and errors will only be
	 * displayed if you redirect the user to a Pines installation. (A query
	 * string is appended to the URL with notice and error text, which Pines
	 * will display.)
	 *
	 * This function will inform the user that they are being redirected if
	 * their browser doesn't support HTTP redirection.
	 *
	 * @param string $url The URL to send the user to.
	 * @param int $code The HTTP code to send to the browser.
	 * @todo Include a page to notify the user about the redirection.
	 */
	public function redirect($url, $code = 303) {
		$notices = $this->page->get_notice();
		$errors = $this->page->get_error();
		if ($notices || $errors) {
			pines_session('write');
			if ($notices)
				$_SESSION['p_notices'] = $notices;
			if ($errors)
				$_SESSION['p_errors'] = $errors;
			pines_session('close');
		}
		header('Location: '.$url);
		$code_strings = array(
			300 => '300 Multiple Choices',
			301 => '301 Moved Permanently',
			302 => '302 Found',
			303 => '303 See Other',
			304 => '304 Not Modified',
			305 => '305 Use Proxy',
			306 => '306 Switch Proxy',
			307 => '307 Temporary Redirect',
			308 => '308 Resume Incomplete',
		);
		header('HTTP/1.1 '.$code_strings[(int) $code]);
		$this->page->override = true;
	}

	/**
	 * Open, close, or destroy sessions.
	 *
	 * Using this method, you can access an existing session for reading or
	 * writing, and close or destroy it.
	 *
	 * Providing a method to open a session for reading allows asynchronous
	 * calls to Pines to work efficiently. PHP will not block during page
	 * requests, so one page taking forever to load doesn't grind a user's whole
	 * session to a halt.
	 *
	 * This method should be the only method sessions are accessed in Pines.
	 * This will allow maximum compatibility between components.
	 *
	 * $option can be one of the following:
	 *
	 * - "read" - Open the session for reading.
	 * - "write" - Open the session for writing. Remember to close it when you
	 *   no longer need write access.
	 * - "close" - Close the session for writing. The session is still readable
	 *   afterward.
	 * - "destroy" - Unset and destroy the session.
	 *
	 * @param string $option The type of access or action requested.
	 */
	public function session($option = 'read') {
		switch ($option) {
			case 'read':
			default:
				if (isset($_SESSION['p_session_access']))
					return;
				if ( @session_start() ) {
					$_SESSION['p_session_access'] = true;
					@session_write_close();
				}
				break;
			case 'write':
				@session_start();
				$_SESSION['p_session_access'] = true;
				break;
			case 'close':
				@session_write_close();
				break;
			case 'destroy':
				@session_unset();
				@session_destroy();
				break;
		}
	}
}

?>
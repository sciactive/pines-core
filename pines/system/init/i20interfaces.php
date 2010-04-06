<?php
/**
 * Define system service interfaces.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (P_SCRIPT_TIMING) pines_print_time('Define Service Interfaces');

/**
 * Objects which hold data from some type of storage.
 * @package Pines
 */
interface data_object_interface {
	/**
	 * Delete the object from storage.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function delete();
	/**
	 * Perform a more strict comparison of this object to another.
	 *
	 * @param mixed &$object The object to compare.
	 * @return bool True or false.
	 */
	public function equals(&$object);
	/**
	 * Check whether this object is in an array.
	 *
	 * If $strict is false, is() is used to compare. If $strict is true,
	 * equals() is used.
	 *
	 * @param array $array The array to search.
	 * @param bool $strict Whether to use stronger comparison.
	 * @return bool True if the object is in the array, false if it isn't or if $array is not an array.
	 */
	public function in_array($array, $strict = false);
	/**
	 * Perform a less strict comparison of this object to another.
	 *
	 * @param mixed &$object The object to compare.
	 * @return bool True or false.
	 */
	public function is(&$object);
	/**
	 * Refresh the object from storage.
	 *
	 * If the object has been deleted from storage, the database cannot be
	 * reached, or a database error occurs, refresh() will return 0.
	 *
	 * @return bool|int False if the data has not been saved, 0 if it can't be refreshed, true on success.
	 */
	public function refresh();
	/**
	 * Save the object to storage.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function save();
}

/**
 * Objects which support abilities, such as users and groups.
 * @package Pines
 */
interface able_object_interface extends data_object_interface {
	/**
	 * Grant an ability.
	 *
	 * Abilities should be named following this form!!
	 *
	 *	 com_componentname/abilityname
	 *
	 * If it is a system ability (ie. not part of a component, substitute
	 * "com_componentname" with "system". The system ability "all" means the
	 * user has every ability available.
	 *
	 * @param string $ability The ability.
	 */
	public function grant($ability);
	/**
	 * Revoke an ability.
	 *
	 * @param string $ability The ability.
	 */
	public function revoke($ability);
}

/**
 * Pines system users.
 * @package Pines
 */
interface user_interface extends able_object_interface {
	/**
	 * Load a user.
	 *
	 * @param int|string $id The ID or username of the user to load, 0 for a new user.
	 */
	public function __construct($id = 0);
	/**
	 * Create a new instance.
	 *
	 * @param int|string $id The ID or username of the user to load, 0 for a new user.
	 */
	public static function factory($id = 0);
	/**
	 * Print a form to edit the user.
	 *
	 * @return module The form's module.
	 */
	public function print_form();
	/**
	 * Add the user to a (secondary) group.
	 *
	 * @param group $group The group.
	 * @return mixed True if the user is already in the group. The resulting array of groups if the user was not.
	 */
	public function addgroup($group);
	/**
	 * Check if the password given is the correct password for the user's
	 * account.
	 *
	 * @param string $password The password in question.
	 * @return bool True or false.
	 */
	public function check_password($password);
	/**
	 * Remove the user from a (secondary) group.
	 *
	 * @param group $group The group.
	 * @return mixed True if the user wasn't in the group. The resulting array of groups if the user was.
	 */
	public function delgroup($group);
	/**
	 * Check whether the user is in a (primary or secondary) group.
	 *
	 * @param mixed $group The group, or the group's GUID.
	 * @return bool True or false.
	 */
	public function ingroup($group = null);
	/**
	 * Check whether the user is a descendent of a group.
	 *
	 * @param mixed $group The group, or the group's GUID.
	 * @return bool True or false.
	 */
	public function is_descendent($group = null);
	/**
	 * Change the user's password.
	 *
	 * @param string $password The new password.
	 * @return string The resulting MD5 sum which is stored in the entity.
	 */
	public function password($password);
	/**
	 * Return the user's timezone.
	 *
	 * @param bool $return_date_time_zone_object Whether to return an object of the DateTimeZone class, instead of an identifier string.
	 * @return string|DateTimeZone The timezone identifier or the DateTimeZone object.
	 */
	public function get_timezone($return_date_time_zone_object = false);
}

/**
 * Pines system groups.
 *
 * Note: When delete() is called all descendants of this group will also be
 * deleted.
 *
 * @package Pines
 */
interface group_interface extends able_object_interface {
	/**
	 * Load a group.
	 *
	 * @param int $id The ID of the group to load, 0 for a new group.
	 */
	public function __construct($id = 0);
	/**
	 * Create a new instance.
	 *
	 * @param int $id The ID of the group to load, 0 for a new group.
	 */
	public static function factory($id = 0);
	/**
	 * Check whether the group is a descendent of a group.
	 *
	 * @param mixed $group The group, or the group's GUID.
	 * @return bool True or false.
	 */
	public function is_descendent($group = null);
	/**
	 * Find the location of the group's current logo image.
	 *
	 * @param bool $rela_location Return a relative URL, instead of a full one.
	 * @return string The URL of the logo image.
	 */
	public function get_logo($rela_location = false);
	/**
	 * Print a form to edit the group.
	 *
	 * @return module The form's module.
	 */
	public function print_form();
}

/**
 * A Pines template.
 * @package Pines
 */
interface template_interface {
	/**
	 * Format a menu.
	 *
	 * @param array $menu The menu.
	 * @return string The menu's resulting code.
	 */
	public function menu($menu);
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
	public function url($component = null, $action = null, $params = array(), $full_location = false);
}

/**
 * Manages Pines configuration.
 * @package Pines
 */
interface configurator_interface {
	/**
	 * Disables a component.
	 *
	 * This function renames the component's directory by adding a dot (.) in
	 * front of the name. This causes Pines to ignore the component.
	 *
	 * @param string $component The name of the component.
	 * @return bool True on success, false on failure.
	 */
	public function disable_component($component);
	/**
	 * Enables a component.
	 *
	 * This function renames the component's directory by removing the dot (.)
	 * in front of the name. This causes Pines to recognize the component.
	 *
	 * @param string $component The name of the component.
	 * @return bool True on success, false on failure.
	 */
	public function enable_component($component);
	/**
	 * Creates and attaches a module which lists configurable components.
	 * @return module The module.
	 */
	public function list_components();
}

/**
 * A configurable component.
 *
 * Must have the following public variables:
 *
 * - $defaults - The configuration defaults.
 * - $config - The current configuration.
 * - $config_keys - The current configuration in an array with key => values.
 * - $info - The info object of the component.
 * - $name - The component.
 *
 * @package Pines
 */
interface configurator_component_interface {
	/**
	 * Load a component's configuration and info.
	 * @param string $component The component to load.
	 */
	public function __construct($component);
	/**
	 * Create a new instance.
	 * @param string $component The component to load.
	 */
	public static function factory($component);
	/**
	 * Get a full config array. (With defaults replaced.)
	 * @return array The array.
	 */
	public function get_full_config_array();
	/**
	 * Check if a component is configurable.
	 * @return bool True or false.
	 */
	public function is_configurable();
	/**
	 * Check if a component is disabled.
	 * @return bool True or false.
	 */
	public function is_disabled();
	/**
	 * Print a form to edit the configuration.
	 * @return module The form's module.
	 */
	public function print_form();
	/**
	 * Print a view of the configuration.
	 * @return module The view's module.
	 */
	public function print_view();
	/**
	 * Write the configuration to the config file.
	 * @return bool True on success, false on failure.
	 */
	public function save_config();
}

/**
 * Logs activity within the framework.
 * @package Pines
 */
interface log_manager_interface {
	/**
	 * Log an entry to the Pines log.
	 *
	 * @param string $message The message to be logged.
	 * @param string $level The level of the message. (debug, info, notice, warning, error, or fatal)
	 * @return bool True on success, false on error.
	 */
	public function log($message, $level = 'info');
}

if (P_SCRIPT_TIMING) pines_print_time('Define Service Interfaces');

?>
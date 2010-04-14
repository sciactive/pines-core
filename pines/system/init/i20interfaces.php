<?php
/**
 * Define system service interfaces.
 *
 * @package Pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
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
 * @property int $guid The UID of the user.
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
	 * @return user A user instance.
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
	public function add_group($group);
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
	public function del_group($group);
	/**
	 * Check whether the user is in a (primary or secondary) group.
	 *
	 * @param mixed $group The group, or the group's GUID.
	 * @return bool True or false.
	 */
	public function in_group($group = null);
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
 * @property int $guid The GID of the group.
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
	 * @return group A group instance.
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
 * @package Pines
 * @property array $defaults The configuration defaults.
 * @property array $config The current configuration.
 * @property array $config_keys The current configuration in an array with key => values.
 * @property array $info The info object of the component.
 * @property string $name The component.
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
	 * @return configurator_component A component configuration object instance.
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

/**
 * Database abstraction layer.
 * @package Pines
 */
interface entity_manager_interface {
	/**
	 * Delete an entity from the database.
	 *
	 * @param entity &$entity The entity to delete.
	 * @return bool True on success, false on failure.
	 */
	public function delete_entity(&$entity);
	/**
	 * Delete an entity by its GUID.
	 *
	 * @param int $guid The GUID of the entity.
	 * @return bool True on success, false on failure.
	 */
	public function delete_entity_by_id($guid);
	/**
	 * Delete a unique ID.
	 *
	 * @param string $name The UID's name.
	 * @return bool True on success, false on failure.
	 */
	public function delete_uid($name);
	/**
	 * Export entities to a local file.
	 *
	 * This is the file format:
	 *
	 * <pre>
	 * # Comments begin with #
	 *    # And can have white space before them.
	 * # This defines a UID.
	 * &lt;name/of/uid&gt;[5]
	 * &lt;another uid&gt;[8000]
	 * # For UIDs, the name is in angle brackets (&lt;&gt;) and the value follows in
	 * #  square brackets ([]).
	 * # This starts a new entity.
	 * {1}[tag,list,with,commas]
	 * # For entities, the GUID is in curly brackets ({}) and the comma
	 * #  separated tag list follows in square brackets ([]).
	 * # Variables are stored like this:
	 * # varname=json_encode(serialize(value))
	 *     abilities="a:1:{i:0;s:10:\"system\/all\";}"
	 *     groups="a:0:{}"
	 *     inherit_abilities="b:0;"
	 *     name="s:5:\"admin\";"
	 * # White space before/after "=" and at beginning/end of line is ignored.
	 *         username  =     "s:5:\"admin\";"
	 * {2}[tag,list]
	 *     another="s:23:\"This is another entity.\";"
	 *     newline="s:1:\"\n\";"
	 * </pre>
	 *
	 * @param string $filename The file to export to.
	 * @return bool True on success, false on failure.
	 */
	public function export($filename);
	/**
	 * Export entities to the client as a downloadable file.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function export_print();
	/**
	 * Get an array of entities.
	 *
	 * GUIDs are integers and start at one (1).
	 *
	 * $options can contain the following key - values:
	 *
	 * - guid - A GUID or array of GUIDs.
	 * - tags - An array of tags. The entity must have each one.
	 * - tags_i - An array of inclusive tags. The entity must have at least one.
	 * - data - An array of key/values corresponding to var/values.
	 * - data_i - An array of inclusive key/values corresponding to var/values.
	 * - match - An array of key/regex corresponding to var/values.
	 * - match_i - An array of inclusive key/regex corresponding to var/values.
	 * - gt - An array of key/numbers corresponding to var/values.
	 * - gt_i - An array of inclusive key/numbers corresponding to var/values.
	 * - gte - An array of key/numbers corresponding to var/values.
	 * - gte_i - An array of inclusive key/numbers corresponding to var/values.
	 * - lt - An array of key/numbers corresponding to var/values.
	 * - lt_i - An array of inclusive key/numbers corresponding to var/values.
	 * - lte - An array of key/numbers corresponding to var/values.
	 * - lte_i - An array of inclusive key/numbers corresponding to var/values.
	 * - ref - An array of key/values corresponding to var/values.
	 * - ref_i - An array of inclusive key/values corresponding to var/values.
	 * - class - The class to create each entity with.
	 * - limit - The limit of entities to be returned.
	 * - offset - The offset from the first (0) to start retrieving entities.
	 *
	 * For regex matching, preg_match() is used.
	 *
	 * The gt/gte/lt/lte options check whether a variable is greater than/
	 * greater or equal to/less than/less than or equal to a number,
	 * respectively.
	 *
	 * For reference searching, the values can be an entity, GUID, or an array
	 * of either. Inclusive ref can't be a single value, as that wouldn't make
	 * sense.
	 *
	 * If a class is specified, it must have a factory() static method which
	 * returns a new instance.
	 *
	 * @param array $options The options to search for.
	 * @return array|null An array of entities, or null on failure.
	 */
	public function get_entities($options = array());
	/**
	 * Get the first entity to match all options.
	 *
	 * $options is the same as in get_entities().
	 *
	 * This function is equivalent to setting $options['limit'] to 1 for
	 * get_entities(), except that it will return an entity or null, instead of
	 * an array.
	 *
	 * @param mixed $options The options to search for, or just a GUID.
	 * @return mixed An entity, or null on failure and nothing found.
	 */
	public function get_entity($options);
	/**
	 * Get the current value of a unique ID.
	 *
	 * @param string $name The UID's name.
	 * @return int|null The UID's value, or null on failure and if it doesn't exist.
	 */
	public function get_uid($name);
	/**
	 * Import entities from a file.
	 *
	 * @param string $filename The file to import from.
	 * @return bool True on success, false on failure.
	 */
	public function import($filename);
	/**
	 * Increment or create a unique ID and return the new value.
	 *
	 * Unique IDs, or UIDs, are ID numbers, similar to GUIDs, but without any
	 * constraints on how they are used. UIDs can be named anything, however a
	 * good naming convention, in order to avoid conflicts, is to use your
	 * component's name, a slash, then a descriptive name of the objects being
	 * identified. E.g. "com_example/widget" or "com_hrm/employee".
	 *
	 * A UID can be used to identify an object when the GUID doesn't suffice. On
	 * a system where a new entity is created many times per second, referring
	 * to something by its GUID may be unintuitive. However, the component
	 * designer is responsible for assigning UIDs to the component's entities.
	 * Beware that if a UID is incremented for an entity, and the entity cannot
	 * be saved, there is no safe, and therefore, no recommended way to
	 * decrement the UID back to its previous value.
	 *
	 * If new_uid() is passed the name of a UID which does not exist yet, one
	 * will be created with that name, and assigned the value 1. If the UID
	 * already exists, its value will be incremented. The new value will be
	 * returned.
	 *
	 * @param string $name The UID's name.
	 * @return int|null The UID's new value, or null on failure.
	 */
	public function new_uid($name);
	/**
	 * Rename a unique ID.
	 *
	 * @param string $old_name The old name.
	 * @param string $new_name The new name.
	 * @return bool True on success, false on failure.
	 */
	public function rename_uid($old_name, $new_name);
	/**
	 * Save an entity to the database.
	 *
	 * If the entity has never been saved (has no GUID), a variable "p_cdate"
	 * is set on it with the current Unix timestamp using microtime(true).
	 *
	 * The variable "p_mdate" is set to the current Unix timestamp using
	 * microtime(true).
	 *
	 * @param mixed &$entity The entity.
	 * @return bool True on success, false on failure.
	 */
	public function save_entity(&$entity);
	/**
	 * Set the value of a UID.
	 *
	 * @param string $name The UID's name.
	 * @param int $value The value.
	 * @return bool True on success, false on failure.
	 */
	public function set_uid($name, $value);
}

/**
 * Database abstraction object.
 *
 * Used to provide a standard, abstract way to access, manipulate, and store
 * data in Pines.
 * 
 * The GUID is not set until the entity is saved. GUIDs must be unique forever,
 * even after deletion. It's the job of the entity manager to make sure no two
 * entities ever have the same GUID.
 * 
 * Tags are used to classify entities. Though not sctrictly necessary, it is
 * *HIGHLY RECOMMENDED* to give every entity your component creates a tag
 * indentical to your component's name, such as 'com_xmlparser'. You don't want
 * to accidentally get another component's entities.
 *
 * Simply calling delete() will not unset the entity. It will still take up
 * memory. Likewise, simply calling unset will not delete the entity from
 * storage.
 *
 * Some notes about equals() and is():
 *
 * equals() performs a more strict comparison of the entity to another. Use
 * equals() instead of the == operator, because the cached entity data causes ==
 * to return false when it should return true. In order to return true, the
 * entity and $object must meet the following criteria:
 *
 * - They must be entities.
 * - They must have equal GUIDs. (Or both can have no GUID.)
 * - They must be instances of the same class.
 * - Their data must be equal.
 *
 * is() performs a less strict comparison of the entity to another. Use is()
 * instead of the == operator when the entity's data may have been changed, but
 * you only care if it is the same entity. In order to return true, the entity
 * and $object must meet the following criteria:
 *
 * - They must be entities.
 * - They must have equal GUIDs. (Or both can have no GUID.)
 * - If they have no GUIDs, their data must be equal.
 *
 * Some notes about saving entities in other entity's variables:
 *
 * The entity class often uses references to store an entity in another entity's
 * variable or array. The reference is stored as an array with the values:
 *
 * - 0 => The string 'pines_entity_reference'
 * - 1 => The reference entity's GUID.
 * - 2 => The reference entity's class name.
 *
 * Since the reference entity's class name is stored in the reference on the
 * entity's first save and used to retrieve the reference entity using the same
 * class, if you change the class name in an update, you need to reassign the
 * reference entity and save to storage.
 *
 * When an entity is loaded, it does not request its referenced entities from
 * the entity manager. This is done the first time the variable/array is
 * accessed. The referenced entity is then stored in a cache, so if it is
 * altered elsewhere, then accessed again through the variable, the changes will
 * *not* be there. Therefore, you should take great care when accessing entities
 * from multiple variables. If you might be using a referenced entity again
 * later in the code execution (after some other processing occurs), it's
 * recommended to call clear_cache().
 *
 * @package Pines
 * @property int $guid The GUID of the entity.
 * @property array $tags Array of the entity's tags.
 */
interface entity_interface extends data_object_interface {
	/**
	 * Add one or more tags. (Same as add_tag)
	 *
	 * @param mixed $tag,... List or array of tags.
	 */
	public function __construct();
	/**
	 * Create a new instance.
	 * @return entity An entity instance.
	 */
	public static function factory();
	/**
	 * Add one or more tags.
	 *
	 * @param mixed $tag,... List or array of tags.
	 */
	public function add_tag();
	/**
	 * Clear the cache of referenced entities.
	 *
	 * Calling this function ensures that the next time a referenced entity is
	 * accessed, it will be retrieved from the entity manager.
	 */
	public function clear_cache();
	/**
	 * Used to retrieve the data array.
	 *
	 * This should only be used by the entity manager to save the data array
	 * into storage.
	 *
	 * @return array The entity's data array.
	 * @access protected
	 */
	public function get_data();
	/**
	 * Check that the entity has all of the given tags.
	 *
	 * @param mixed $tag,... List or array of tags.
	 * @return bool
	 */
	public function has_tag();
	/**
	 * Used to set the data array.
	 *
	 * This should only be used by the entity manager to push the data array
	 * from storage.
	 *
	 * @param array $data The data array.
	 * @return array The data array.
	 * @access protected
	 */
	public function put_data($data);
	/**
	 * Remove one or more tags.
	 *
	 * @param mixed $tag,... List or array of tags.
	 */
	public function remove_tag();
	/**
	 * Return a Pines Entity Reference for this entity.
	 *
	 * @return array A Pines Entity Reference array.
	 */
	public function to_reference();
}

/**
 * Ability manager.
 *
 * An ability manager works with a user manager to provide an access control
 * system for Pines. The gatekeeper() function is used to check if a user has
 * been granted an ability.
 *
 * @package Pines
 * @property array $abilities Array of defined abilities.
 */
interface ability_manager_interface {
	/**
	 * Add a system managed ability.
	 *
	 * This function is used to let the system know that you will be using an
	 * ability in your component. If you don't let the system know, you will
	 * have to give your users abilities yourself.
	 *
	 * A good way to do this is have the following in an init file (like
	 * i30abilities.php)
	 *
	 * <code>
	 * if ( isset($pines->ability_manager) ) {
	 *	$pines->ability_manager->add('com_whatever', 'firstability', 'title', 'description');
	 *	$pines->ability_manager->add('com_whatever', 'secondability', 'title', 'description');
	 * }
	 * </code>
	 *
	 * @param string $component The component under which to place the ability.
	 * @param string $ability The name of the ability to manage.
	 * @param string $title A descriptive title to display to the user.
	 * @param string $description A description of the ability to display to the user.
	 */
	public function add($component, $ability, $title, $description);
	/**
	 * Get an array of the abilities that a specified component has reported
	 * that it uses.
	 *
	 * @param string $component The component.
	 * @return array The array of abilities.
	 */
	public function get_abilities($component);
}

/**
 * User and group manager.
 *
 * User managers need to hook entity transactions and filter certain
 * functionality based on an access control variable.
 *
 * @package Pines
 * @todo Finish describing user manager's entity obligations.
 */
interface user_manager_interface {
	/**
	 * Check an entity's permissions for the currently logged in user.
	 *
	 * This will check the variable "ac" (Access Control) of the entity. It
	 * should be an object that contains the following properties:
	 *
	 * - user
	 * - group
	 * - other
	 *
	 * The property "user" refers to the entity's owner, "group" refers to all
	 * users in the entity's group and all ancestor groups, and "other" refers
	 * to any user who doesn't fit these descriptions.
	 *
	 * Each variable should be either 0, 1, 2, or 3. If it is 0, the user has no
	 * access to the entity. If it is 1, the user has read access to the entity.
	 * If it is 2, the user has read and write access to the entity. If it is 3,
	 * the user has read, write, and delete access to the entity.
	 *
	 * "ac" defaults to:
	 *
	 * - user = 3
	 * - group = 3
	 * - other = 0
	 *
	 * The following conditions will result in different checks, which determine
	 * whether the check passes:
	 *
	 * - No user is logged in. (Always returned, should be managed with abilities.)
	 * - The entity has no uid and no gid. (Always returned.)
	 * - The user has the "system/all" ability. (Always returned.)
	 * - The entity is the user. (Always returned.)
	 * - It is the user's primary group. (Always returned.)
	 * - The entity is a user or group. (Always returned.)
	 * - Its UID is the user. (It is owned by the user.) (Check user AC.)
	 * - Its GID is the user's primary group. (Check group AC.)
	 * - Its GID is one of the user's secondary groups. (Check group AC.)
	 * - Its GID is a child of one of the user's groups. (Check group AC.)
	 * - None of the above. (Check other AC.)
	 *
	 * @param object &$entity The entity to check.
	 * @param int $type The lowest level of permission to consider a pass. 1 is read, 2 is write, 3 is delete.
	 * @return bool Whether the current user has at least $type permission for the entity.
	 */
	public function check_permissions(&$entity, $type = 1);
	/**
	 * Fill the $_SESSION['user'] variable with the logged in user's data.
	 *
	 * Also sets the default timezone to the user's timezone.
	 *
	 * This must be called by at least i40 in the init script processing.
	 */
	public function fill_session();
	/**
	 * Check to see if a user has an ability.
	 *
	 * If $ability and $user are null, it will check to see if a user is
	 * currently logged in.
	 *
	 * If the user has the "system/all" ability, this function will return true.
	 *
	 * @param string $ability The ability.
	 * @param user $user The user to check. If none is given, the current user is used.
	 * @return bool
	 */
	public function gatekeeper($ability = NULL, $user = NULL);
	/**
	 * Gets an array of the components which can be a default component.
	 *
	 * The way a component can be a user's default components is to have a
	 * "default" action, which loads what the user will see when they first log
	 * on.
	 *
	 * @return array The array of component names.
	 */
	public function get_default_component_array();
	/**
	 * Gets an array of a group's descendendents.
	 *
	 * If no parent is given, get_group_descendents() will start with all top
	 * level groups. (It will return all top level groups' descendents.)
	 *
	 * get_group_descendents() returns an array of a group's descendents.
	 *
	 * @param group $parent The group to descend from.
	 * @return array The array of groups.
	 */
	public function get_group_descendents($parent = NULL);
	/**
	 * Gets a group's groupname by its GID.
	 *
	 * @param int $id The group's GID.
	 * @return string|null The groupname if the group exists, null if it doesn't.
	 */
	public function get_groupname($id);
	/**
	 * Gets all groups.
	 *
	 * @return array An array of group entities.
	 */
	public function get_groups();
	/**
	 * Gets a user's username by its UID.
	 *
	 * @param int $id The user's UID.
	 * @return string|null The username if the user exists, null if it doesn't.
	 */
	public function get_username($id);
	/**
	 * Gets all users.
	 *
	 * @return array An array of user entities.
	 */
	public function get_users();
	/**
	 * Logs the given user into the system.
	 *
	 * @param user $user The user.
	 * @return bool True on success, false on failure.
	 */
	public function login($user);
	/**
	 * Logs the current user out of the system.
	 */
	public function logout();
	/**
	 * Creates and attaches a module which let's the user log in.
	 *
	 * @param string $position The position in which to place the module.
	 * @return module The new module.
	 */
	public function print_login($position = 'content');
	/**
	 * Kick the user out of the current page.
	 *
	 * Note that this method completely terminates execution of the script when
	 * it is called. Code after this function is called will not run.
	 *
	 * @param string $message An optional message to display to the user.
	 * @param string $url An optional URL to be included in the query data of the redirection url.
	 */
	public function punt_user($message = null, $url = null);
}

/**
 * Content editor.
 * @package Pines
 */
interface editor_interface {
	/**
	 * Load the editor.
	 *
	 * This will transform any textareas with the "peditor" class into editors
	 * and any textareas with the "peditor_simple" class into simple editors.
	 *
	 * Simple editors may be the same as editors, depending on the
	 * implementation.
	 */
	public function load();
}

if (P_SCRIPT_TIMING) pines_print_time('Define Service Interfaces');

?>
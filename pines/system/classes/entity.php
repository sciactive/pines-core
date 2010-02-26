<?php
/**
 * entity class.
 *
 * @package Pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Used to provide an abstract way to represent and store data in Pines.
 *
 * ALWAYS use tags to categorize your entities. You don't want to accidentally
 * get another component's entities, so make a tag that's the name of your
 * component.
 *
 * Some notes about saving entities in other entity's variables:
 *
 * The entity class uses references to store an entity in another entity's
 * variable or array. The reference is stored as an array with the values:
 *
 * - 0 => The string 'pines_entity_reference'
 * - 1 => The reference entity's GUID.
 * - 2 => The reference entity's class name.
 *
 * Since the reference entity's class name is stored in the reference on the
 * entity's first save and used to retrieve the reference entity using the same
 * class, if you change the class name, you need to reassign the reference
 * entity and save to storage.
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
 */
class entity extends p_base {
	/**
	 * The GUID of the entity.
	 *
	 * The GUID is not set until the entity is saved. GUIDs must be unique
	 * forever, even after deletion. It's the job of the entity manager to make
	 * sure no two entities ever have the same GUID.
	 *
	 * @var int
	 */
	public $guid = null;
	/**
	 * Tags are used to classify entities.
	 *
	 * Though not sctrictly necessary, it is HIGHLY RECOMMENDED to give every
	 * entity your component creates a tag indentical to your component's name.
	 * Such as 'com_xmlparser'.
	 *
	 * @var array
	 */
	public $tags = array();
	/**
	 * The array used to store each variable assigned to an entity.
	 *
	 * @var array
	 * @access protected
	 */
	protected $data = array();
	/**
	 * The array used to store referenced entities.
	 *
	 * This technique allows your code to see another entity as a variable,
	 * while storing only a reference.
	 *
	 * @var array
	 * @access protected
	 */
	protected $entity_cache = array();

	/**
	 * Add one or more tags. (Same as add_tag)
	 *
	 * @param mixed $tag,... List or array of tags.
	 */
	public function __construct() {
		$args = func_get_args();
		if (!empty($args))
			call_user_func_array(array($this, 'add_tag'), $args);
	}

	/**
	 * Create a new instance.
	 */
	public static function factory() {
		global $pines;
		$class = get_class();
		$entity = new $class;
		$pines->hook->hook_object($entity, $class.'->', false);
		$args = func_get_args();
		if (!empty($args))
			call_user_func_array(array($entity, 'add_tag'), $args);
		return $entity;
	}

	/**
	 * Retrieve a variable.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 *
	 * @param string $name The name of the variable.
	 * @return mixed The value of the variable or nothing if it doesn't exist.
	 */
	public function &__get($name) {
		global $pines;
		// Check for an entity first.
		if (array_key_exists($name, $this->entity_cache)) {
			if ($this->entity_cache[$name] === 0) {
				// The entity hasn't been loaded yet, so load it now.
				$this->entity_cache[$name] = $pines->entity_manager->get_entity(array('guid' => $this->data[$name][1], 'class' => $this->data[$name][2]));
			}
			return $this->entity_cache[$name];
		}
		// If it's not an entity, return the regular value.
		if (is_array($this->data[$name])) {
			// But, if it's an array, check all the values for entity references, and change them.
			array_walk($this->data[$name], array($this, 'reference_to_entity'));
		}
		return $this->data[$name];
	}

	/**
	 * Checks whether a variable is set.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 *
	 * @param string $name The name of the variable.
	 * @return bool
	 */
	public function __isset($name) {
		return isset($this->data[$name]);
	}

	/**
	 * Sets a variable.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 *
	 * @param string $name The name of the variable.
	 * @param string $value The value of the variable.
	 * @return mixed The value of the variable.
	 */
	public function __set($name, $value) {
		if ((is_a($value, 'entity') || is_a($value, 'hook_override')) && isset($value->guid)) {
			// This is an entity, so we don't want to store it in our data array.
			$this->entity_cache[$name] = $value;
			// Store a reference to the entity (its GUID and the class it was loaded as).
			// We don't want to manipulate $value itself, because it could be a variable that the program is still using.
			$save_value = $value->to_reference();
		} else {
			// This is not an entity, so if it was one, delete the cached entity.
			if (isset($this->entity_cache[$name]))
				unset($this->entity_cache[$name]);
			// Store the actual value passed.
			$save_value = $value;
			// If the variable is an array, look through it and change entities to references.
			if (is_array($save_value))
				array_walk_recursive($save_value, array($this, 'entity_to_reference'));
		}
		
		return ($this->data[$name] = $save_value);
	}

	/**
	 * Unsets a variable.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 *
	 * @param string $name The name of the variable.
	 */
	public function __unset($name) {
		if (isset($this->entity_cache[$name]))
			unset($this->entity_cache[$name]);
		unset($this->data[$name]);
	}

	/**
	 * Add one or more tags.
	 *
	 * @param mixed $tag,... List or array of tags.
	 */
	public function add_tag() {
		if (is_array(func_get_arg(0))) {
			$tag_array = func_get_arg(0);
		} else {
			$tag_array = func_get_args();
		}
		foreach ($tag_array as $tag) {
			array_push($this->tags, $tag);
		}
	}

	/**
	 * Clear the cache of referenced entities.
	 *
	 * Calling this function ensures that the next time a referenced entity is
	 * accessed, it will be retrieved from the entity manager.
	 */
	public function clear_cache() {
		foreach ($this->entity_cache as &$value) {
			$value = 0;
		}
	}

	/**
	 * Delete the entity from storage.
	 *
	 * Simply calling delete() will not unset the entity. It will still take up
	 * memory. Likewise, simply calling unset will not delete the entity from
	 * storage.
	 *
	 * @return mixed Returns what the entity manager's delete_entity function returns.
	 */
	public function delete() {
		global $pines;
		return $pines->entity_manager->delete_entity($this);
	}

	/**
	 * Check if an item is an entity, and if it is, convert it to a reference.
	 *
	 * @param mixed $item The item to check.
	 * @param mixed $key Unused.
	 * @access private
	 */
	private function entity_to_reference(&$item, $key) {
		if ((is_a($item, 'entity') || is_a($item, 'hook_override')) && isset($item->guid)) {
			// This is an entity, so we should put it in the entity cache.
			if (!isset($this->entity_cache["reference_guid: {$item->guid}"]))
				$this->entity_cache["reference_guid: {$item->guid}"] = clone $item;
			// Make a reference to the entity (its GUID) and the class the entity was loaded as.
			$item = $item->to_reference();
		}
	}

	/**
	 * Perform a more strict comparison of this entity to another.
	 *
	 * In order to return true, this and $object must meet the following
	 * criteria:
	 *
	 * - They must be entities.
	 * - They must have equal GUIDs. (Or both can have no GUID.)
	 * - They must be instances of the same class.
	 * - Their data must be equal.
	 *
	 * Use this instead of the == operator, because the cached entity data
	 * causes == to return false when it should return true.
	 *
	 * @param mixed $object The object to compare.
	 * @return bool True or false.
	 */
	public function equals(&$object) {
		if (!is_a($object, 'entity') || is_a($item, 'hook_override'))
			return false;
		if (isset($this->guid) || isset($object->guid)) {
			if ($this->guid != $object->guid)
				return false;
		}
		if (get_class($object) != get_class($this))
			return false;
		$ob_data = $object->get_data();
		$my_data = $this->get_data();
		return ($ob_data == $my_data);
	}

	/**
	 * Used to retrieve the data array.
	 *
	 * This should only be used by the entity manager to save the data array
	 * into storage.
	 *
	 * @return array The entity's data array.
	 * @access protected
	 */
	public function get_data() {
		// First, walk though the data and convert any entities to references.
		array_walk_recursive($this->data, array($this, 'entity_to_reference'));
		return $this->data;
	}

	/**
	 * Check that the entity has all of the given tags.
	 *
	 * @param mixed $tag,... List or array of tags.
	 * @return bool
	 */
	public function has_tag() {
		if (is_array(func_get_arg(0))) {
			$tag_array = func_get_arg(0);
		} else {
			$tag_array = func_get_args();
		}
		foreach ($tag_array as $tag) {
			if ( !is_array($this->tags) || !in_array($tag, $this->tags) )
				return false;
		}
		return true;
	}

	/**
	 * Check whether this entity is in an array.
	 *
	 * If $strict is false, is() is used to compare. If $strict is true,
	 * equals() is used.
	 *
	 * @param array $array The array to search.
	 * @param bool $strict Whether to use stronger comparison.
	 * @return bool True if the entity is in the array, false if it isn't or if $array is not an array.
	 */
	public function in_array($array, $strict = false) {
		if (!is_array($array))
			return false;
		foreach ($array as $cur_entity) {
			if ($strict ? $this->equals($cur_entity) : $this->is($cur_entity))
				return true;
		}
		return false;
	}

	/**
	 * Perform a less strict comparison of this entity to another.
	 *
	 * In order to return true, this and $object must meet the following
	 * criteria:
	 *
	 * - They must be entities.
	 * - They must have equal GUIDs. (Or both can have no GUID.)
	 * - If they have no GUIDs, their data must be equal.
	 *
	 * Use this instead of the == operator when the entity's data may have
	 * been changed, but you only care if it is the same entity.
	 *
	 * @todo Check tags too.
	 * @param mixed $object The object to compare.
	 * @return bool True or false.
	 */
	public function is(&$object) {
		if (!(is_a($object, 'entity') || is_a($object, 'hook_override')))
			return false;
		if (isset($this->guid) || isset($object->guid)) {
			return ($this->guid == $object->guid);
		} else {
			$ob_data = $object->get_data();
			$my_data = $this->get_data();
			return ($ob_data == $my_data);
		}
	}

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
	public function put_data($data) {
		if (!is_array($data))
			$data = array();
		// Erase the entity cache.
		$this->entity_cache = array();
		foreach($data as $name => $value) {
			if (is_array($value) && $value[0] === 'pines_entity_reference') {
				// Don't load the entity yet, but make the entry in the array,
				// so we know it is an entity reference. This will speed up
				// retrieving entities with lots of references, especially
				// recursive references.
				$this->entity_cache[$name] = 0;
			}
		}
		return ($this->data = $data);
	}

	/**
	 * Check if an item is a reference, and if it is, convert it to an entity.
	 *
	 * This function will recurse into deeper arrays.
	 *
	 * @param mixed $item The item to check.
	 * @param mixed $key Unused.
	 * @access private
	 */
	private function reference_to_entity(&$item, $key) {
		global $pines;
		if (is_array($item)) {
			if ($item[0] === 'pines_entity_reference') {
				if (!isset($this->entity_cache["reference_guid: {$item[1]}"]))
					$this->entity_cache["reference_guid: {$item[1]}"] = $pines->entity_manager->get_entity(array('guid' => $item[1], 'class' => $item[2]));
				$item = $this->entity_cache["reference_guid: {$item[1]}"];
			} else {
				array_walk($item, array($this, 'reference_to_entity'));
			}
		}
	}

	/**
	 * Refresh the entity from storage.
	 *
	 * If the entity has been deleted from storage, the database cannot be
	 * reached, or a database error occurs, refresh() will return 0.
	 *
	 * @return bool|int False if the entity has no GUID, 0 if it can't be refreshed, true on success.
	 */
	public function refresh() {
		if (!is_int($this->guid))
			return false;
		global $pines;
		$refresh = $pines->entity_manager->get_entity(array('guid' => $this->guid, 'class' => get_class($this)));
		if (is_null($refresh))
			return 0;
		$this->tags = $refresh->tags;
		$this->put_data($refresh->get_data());
		return true;
	}

	/**
	 * Remove one or more tags.
	 *
	 * @param mixed $tag,... List or array of tags.
	 */
	public function remove_tag() {
		if (is_array(func_get_arg(0))) {
			$tag_array = func_get_arg(0);
		} else {
			$tag_array = func_get_args();
		}
		foreach ($tag_array as $tag) {
			// Can't use array_search, because $tag may exist more than once.
			foreach ($this->tags as $cur_key => $cur_tag) {
				if ( $cur_tag === $tag )
					unset($this->tags[$cur_key]);
			}
		}
		$this->tags = array_values($this->tags);
	}

	/**
	 * Save the entity to storage.
	 *
	 * @return mixed Returns what the entity manager's save_entity function returns.
	 */
	public function save() {
		global $pines;
		return $pines->entity_manager->save_entity($this);
	}

	/**
	 * Return a Pines Entity Reference for this entity.
	 *
	 * @return array A Pines Entity Reference array.
	 */
	public function to_reference() {
		return array('pines_entity_reference', $this->guid, get_class($this));
	}
}

?>

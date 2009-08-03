<?php
/**
 * Entity Class.
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
 * ALWAYS check tags after retrieving an entity.
 *
 * Some notes about saving entities in other entity's variables (sub-entity):
 *
 * --To avoid all this confusion, don't save sub-entities, and try not to even
 * use them. Think about parent entities, DynamicConfig, or tags instead. :)
 *
 * If you still MUST save your entities in other entities:
 *							(all this only applies to *saved* sub-entities)
 *
 * -When you save a sub-entity, it effectively duplicates the data in the
 * database. Once as data of the owner, and again as its own entity. This
 * is not efficient and difficult to keep track of!!
 *
 * -ALWAYS delete sub-entities first, unless they are to survive their owner,
 * entity managers don't delete sub-entities when you delete their owner.
 *
 * -When you delete() a sub-entity, it doesn't dissappear, it just gets removed
 * from the database as an entity and loses its GUID. You should subsequently
 * unset() it if you want it out of your owner entity.
 *
 * -Remember to SAVE your owner after deleting and unsetting sub-entities.
 *
 * -If you just unset() a saved sub-entity without delete()ing it, it will still
 * be an entity in the database, just no longer data in the owner.
 *
 * -Saving an owner does not save its sub-entity. if you load the owner, its
 * sub-entity will have the new data, but if you load the sub-entity, it will
 * have the old data, so save both sub-entity and owner (sub-entities first).
 *
 * -Also, saving sub-entities will not save their data in the owner. SAVE ALL
 * SUB-ENTITIES FIRST, THEN OWNERS.
 *
 * Now, after having said all that, you can see that sub-entities are very
 * innefficient. The $entity->parent system was designed to replace this
 * functionality, and though not as versatile, it is MUCH more efficient. Try to
 * avoid using sub-entities at all cost!
 *
 * @package Pines
 */
class entity extends p_base {
    /**
     * The GUID of the entity.
     *
     * The GUID is not set until the entity is saved. GUIDs must be unique
     * forever. It's the job of the entity manager to make sure no two entities
     * ever have the same GUID.
     *
     * @var int
     */
	public $guid = null;
    /**
     * The GUID of the parent entity.
     *
     * You can use this feature to create complex hierarchies of entities.
     * $parent defaults to the current user ID, if one is logged in.
     *
     * @var int
     */
	public $parent = null;
    /**
     * Tags are used to classify entities.
     *
     * Though not sctrictly necessary, it is a VERY good idea to give every
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
     * @internal
     */
    protected $data = array();

    /**
     * Sets the entity's parent to the current user.
     *
     * This method is called by PHP as soon as the entity is instantiated.
     *
     * @internal
     */
    public function __construct() {
        if (isset($_SESSION['user_id']))
            $this->parent = $_SESSION['user_id'];
    }

    /**
     * Retrieve a variable.
     *
     * You do not need to explicitly call this method. It is called by PHP when
     * you access the variable normally.
     *
     * @param string $name The name of the variable.
     * @return mixed The value of the variable or null if it does not exist.
     * @internal
     */
    public function &__get($name) {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        return null;
    }

    /**
     * Checks whether a variable is set.
     *
     * You do not need to explicitly call this method. It is called by PHP when
     * you access the variable normally.
     *
     * @param string $name The name of the variable.
     * @return bool
     * @internal
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
     * @internal
     */
    public function __set($name, $value) {
        return ($this->data[$name] = $value);
    }

    /**
     * Unsets a variable.
     *
     * You do not need to explicitly call this method. It is called by PHP when
     * you access the variable normally.
     *
     * @param string $name The name of the variable.
     * @internal
     */
    public function __unset($name) {
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
     * Delete the entity from the database.
     *
     * Simply calling delete() will not unset your entity, so it will still take
     * up memory. Also, calling unset will not delete your entity from the
     * database.
     *
     * @return mixed Returns what the entity manager's delete_entity function returns.
     */
	public function delete() {
		global $config;
		return $config->entity_manager->delete_entity($this);
	}

	/**
	 * Used to retrieve the data array.
     *
     * This should only be used by the entity manager to save the data array
     * into the database.
     *
     * @return array The entity's data array.
     * @internal
	 */
	public function get_data() {
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
			if ( !is_array($this->tags) || !in_array($tag, $this->tags) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Used to set the data array.
     *
     * This should only be used by the entity manager to push the data array
     * from the database.
     *
     * @param array $data The data array.
     * @return array The data array.
     * @internal
	 */
	public function put_data($data) {
		return ($this->data = $data);
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
     * Save the entity to the database.
     *
     * @return mixed Returns what the entity manager's save_entity function returns.
     */
	public function save() {
		global $config;
		return $config->entity_manager->save_entity($this);
	}
}

?>
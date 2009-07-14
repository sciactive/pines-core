<?php
defined('D_RUN') or die('Direct access prohibited');

class DynamicConfig { }
$config = new DynamicConfig;

// Program Variables: Usually there is no need to edit these.

$config->program_title = "Dandelion";

$config->program_version = "0.12 Alpha";

// Program Settings

// The whole location of Dandelion. (URL)
// ****End this path with a slash!****
$config->full_location = "http://localhost/dandelion/trunk/";

// The location of Dandelion relative to your server. (URL)
// If it is in the root of the server, just put "/".
// ****End this path with a slash!****
$config->rela_location = "/dandelion/trunk/";

// The directory to store uploaded files. (Real Path / URL)
// This should be the real, relative path and the relative URL.
// ****End this path with a slash!****
$config->setting_upload = "media/";

// Options

// The default title at the top of each page.
$config->option_title = "Dandelion";

// The copyright notice at the bottom of each page.
$config->option_copyright_notice = "&copy; 2009 Hunter Perrin. All Rights Reserved. Powered by ".$config->program_title." version ".$config->program_version.".";

// The default template.
$config->default_template = "dandelion";

// Allow the template to be overriden by adding ?template=whatever
$config->allow_template_override = true;

// Use url rewriting engine.
$config->url_rewriting = true;

// Use Apache .htaccess with mod_rewrite. (Rename htaccess.txt to .htaccess before using.)
//TODO: Change this to false before any release.
$config->use_htaccess = false;

// You do not need to edit anything below this line.

/*
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
 * -ALWAYS delete sub-entities first, unless they are to survive their owner,
 * entity managers don't delete sub-entities when you delete their owner.
 * -When you delete() a sub-entity, it doesn't dissappear, it just gets removed
 * from the database as an entity and loses its GUID. You should subsequently
 * unset() it if you want it out of your owner entity.
 * -Remember to SAVE your owner after deleting and unsetting sub-entities.
 * -If you just unset() a saved sub-entity without delete()ing it, it will still
 * be an entity in the database, just no longer data in the owner.
 * -Saving an owner does not save its sub-entity. if you load the owner, its
 * sub-entity will have the new data, but if you load the sub-entity, it will
 * have the old data, so save both sub-entity and owner (sub-entities first).
 * -Also, saving sub-entities will not save their data in the owner. SAVE ALL
 * SUB-ENTITIES FIRST, THEN OWNERS.
 *
 * Now, after having said all that, you can see that sub-entities are very
 * innefficient. The $entity->parent system was designed to replace this
 * functionality, and though not as versatile, it is MUCH more efficient. Try to
 * avoid using sub-entities at all cost!
 */

// Entity Class
class entity {
	public $guid = null;
	public $parent = null;
	public $tags = array();
    protected $data = array();

    public function __construct() {
        $this->parent = $_SESSION['user_id'];
    }

    public function &__get($name) {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        /*$trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);*/
        return null;
    }

    public function __isset($name) {
        return isset($this->data[$name]);
    }

    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    public function __unset($name) {
        unset($this->data[$name]);
    }

	/*
	 * Add one or more tags.
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

	public function delete() {
		global $config;
		return $config->entity_manager->delete_entity($this);
	}

	/*
	 * Do not use get_data()!!
	 * It should only be used by entity managers when saving an entity.
	 */
	public function get_data() {
		return $this->data;
	}

	/*
	 * Check that entity has all of the given tags.
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

	/*
	 * Do not use put_data()!!
	 * It should only be used by entity managers when initializing an entity.
	 */
	public function put_data($data) {
		$this->data = $data;
	}

	/*
	 * Remove one or more tags.
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

	public function save() {
		global $config;
		return $config->entity_manager->save_entity($this);
	}
}

class template {
	function url($component = null, $action = null, $params = array(), $encode_entities = true, $full_location = false) {
		global $config;
		if ( is_null($params) ) $params = array();
		if ( $config->allow_template_override && isset($_REQUEST['template']) )
			$params['template'] = $_REQUEST['template'];
		$return = ($full_location) ? $config->full_location : $config->rela_location;
		if ( is_null($component) && empty($params) )
			return $return;
		if ( $config->url_rewriting ) {
			if ( !$config->use_htaccess )
				$return .= D_INDEX.'/';
			if ( !is_null($component) ) {
				// Get rid of 'com_'
				$return .= substr($component, 4).'/';
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
			$return .= ($config->use_htaccess) ? '?' : D_INDEX.'?';
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

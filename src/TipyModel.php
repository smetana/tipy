<?php

// ==================================================================
// Simple Model class
//
// ==================================================================

// ==================================================================
// Default Conventions:
// ==================================================================
//
//  Class name:  BlogPost                     singular CamelCase
//  Table name:  blog_posts                   plural lowercase underscored
// Primary Key:  id                           allways id
//  Field Name:  some_field                   lowercase underscored
//  Model Attr:  someField                    camelCase based on field name
// Foreign key:  blog_comment_id              foreign table + "_id"
// ==================================================================
//
// This conventions can be changed by extending this class and redefining following methods:
//      classNameToTableName(),
//      fieldNameToAttrName(),
//      tableForeignKeyFieldName(),
//      classForeignKeyAttr(),
//      getCurrentTime()
// and folowing consts:
//      CREATED_AT,
//      UPDATED_AT
//




// ==================================================================
// Model Samples
//
// class User extends TipyModel {
//
//    protected $hasMany = [
//        'posts' => ['class' => 'BlogPost', 'dependent' => 'nullify'],
//        'positive_posts' => ['class' => 'BlogPost', 'conditions' => 'rating > 0', 'dependent' => 'nullify'],
//        'relations' => ['class' => 'UserGroup', 'dependent' => 'delete']
//    );
//
//    protected $hasManyThrough = [
//        'groups' => ['class' => 'Group', 'through' => 'UserGroup'],
//        'friends' => ['class' => 'User', 'through' => 'Friend', 'foreign_key' => 'person_id', 'through_key' => 'friend_id')
//    ];
//
//    protected $hasOne = [
//        'profile' => ['class' => 'Profile', 'dependent' => 'delete', 'foreign_key' => 'user_id']
//    ];
//
//    public function validate() {
//        if (!$this->login) throw new TipyValidtionException('Login should not be blank!');
//        if (!$this->password) throw new TipyValidtionException('Password should not be blank!');
//        if (!$this->email) throw new TipyValidtionException('Email should not be blank!');
//    }
// }
//
// class Profile extends TipyModel {
//
//    protected $belongsTo = [
//        'user' => ['class' => 'User']
//    ];
//
// }
// ==================================================================

// ==================================================================
// TODO: add 'extend' to hasManyThrough asoc
// ==================================================================

// ==================================================================
// Notes about associations caching:
// ==================================================================
// Associations are cached. So if you ask $post->comments more than once
// only one query will be executed (first time), then comments will always
// be taken from cache.
//
// Downside of this approach, is that if comments were modified or
// changed in the database cache doesn't know about it.
// To reset associations cache use $post->reload()
//
// Associations with queries are not cached. This means that
// $post->comments(['order' => 'created_at desc'])
// will allways execute query
//
// In short always look for parethesis:
//    $post->comments; is cached
//    $post->comments(...); is not

class TipyModelException extends Exception {}
class TipyValidationException extends Exception {}

class TipyModel extends TipyDAO {

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected static $globalReflections = [];

    public $className;
    public $table;
    public $attributes;
    public $fields;
    public $reflections;
    public $data;
    public $isDeletedRecord;
    public $associationsCache;

    protected $hasMany;
    protected $hasOne;
    protected $belongsTo;
    protected $hasManyThrough;

    public function __construct($attrs = null) {
        parent::__construct();
        $this->className = get_class($this);
        $this->table = $this->classNameToTableName($this->className);
        $this->makeReflection();
        $this->isDeletedRecord = false;
        $this->associationsCache = [];
        if ($attrs) {
            if (array_key_exists('id', $attrs)) {
                $this->id = $attrs["id"];
                $this->reload();
            }
            foreach ($attrs as $name => $value) {
                if (!in_array($name, $this->attributes)) {
                    throw new TipyModelException("Unknown property '".$name."' for ".$this->className);
                }
                $this->data[$name] = $value;
            }
        }
    }

    public function __toString() {
        return '<'.$this->className.'>#'.$this->id;
    }

    public function __set($attr, $value) {
        $this->checkAttribute($attr);
        $this->data[$attr] = $value;
    }

    public function __get($attr) {
        if (method_exists($this, $attr) and is_callable([$this, $attr])) {
            return call_user_func([$this, $attr]);
        }
        if ($this->belongsTo && array_key_exists($attr, $this->belongsTo)) {
            return $this->findBelongsTo($attr);
        }
        if ($this->hasMany && array_key_exists($attr, $this->hasMany)) {
            return $this->findHasMany($attr);
        }
        if ($this->hasOne && array_key_exists($attr, $this->hasOne)) {
            return $this->findHasOne($attr);
        }
        if ($this->hasManyThrough && array_key_exists($attr, $this->hasManyThrough)) {
            return $this->findHasManyThrough($attr);
        }
        $this->checkAttribute($attr);
        return array_key_exists($attr, $this->data) ? $this->data[$attr] : null;
    }

    public function __call($name, $args) {
        if (isset($this->hasMany[$name])) {
            return $this->findHasMany($name, $args[0]);
        }
        if (isset($this->hasManyThrough[$name])) {
            return $this->findHasManyThrough($name, $args[0]);
        }
        throw new TipyModelException("Unknown method '".$name."' for ".$this->className);
    }

    public function checkAttribute($name) {
        if (!in_array($name, $this->attributes)) {
            throw new TipyModelException("Unknown property '".$name."' for ".$this->className);
        }
    }

    // --------------------------------------------------------------
    // Asks table about model attributes
    // --------------------------------------------------------------
    protected function makeReflection() {
        $this->data = [];
        if (array_key_exists($this->className, self::$globalReflections)) {
            $this->fields = self::$globalReflections[$this->className]["fields"];
            $this->attributes = self::$globalReflections[$this->className]["attributes"];
            $this->reflections = self::$globalReflections[$this->className]["reflections"];
        } else {
            $this->fields = [];
            $this->attributes = [];
            $this->reflections = [];
            $fields = $this->queryAllRows("show columns from ".$this->table);
            foreach ($fields as $field) {
                $fieldName = $field["Field"];
                $attrName = $this->fieldNameToAttrName($fieldName);
                $this->fields[] = $fieldName;
                $this->attributes[] = $attrName;
                $this->reflections[$fieldName] = $attrName;
            }
            // Store reflections for future use of this class
            self::$globalReflections[$this->className] = [];
            self::$globalReflections[$this->className]["fields"] = $this->fields;
            self::$globalReflections[$this->className]["attributes"] = $this->attributes;
            self::$globalReflections[$this->className]["reflections"] = $this->reflections;
        }
    }

    // --------------------------------------------------------------
    // Creale model instance and load correspondent row from db
    // Note that this method is static.
    //
    // Usage:
    //     $post = BlogPost::load(123);
    // --------------------------------------------------------------
    public static function load($id) {
        $className = get_called_class();
        $instance = new $className;
        $result =  $instance->queryRow(
            "select * from ".$instance->table." where id=?",
            [$id]
        );
        if (!$result) {
            return null;
        }
        $instance = self::instanceFromResult($instance, $result);
        return $instance;
    }

    public function isNewRecord() {
        return !$this->id;
    }

    // --------------------------------------------------------------
    // Save model's row
    // --------------------------------------------------------------
    public function save() {
        if ($this->isDeletedRecord) {
            throw new TipyModelException('Unable to save deleted model');
        }
        $this->validate();
        if ($this->isNewRecord()) {
            if (in_array(static::CREATED_AT, $this->fields)) {
                if (!$this->createdAt) {
                    $this->createdAt = $this->getCurrentTime();
                }
            }
            $result = $this->createNewRecord();
        } else {
            if (in_array(static::UPDATED_AT, $this->fields)) {
                $this->updatedAt = $this->getCurrentTime();
            }
            $result = $this->updateRecord();
        }
        return $result;
    }

    // --------------------------------------------------------------
    // Update model's row attribute
    // --------------------------------------------------------------
    public function update($name, $value) {
        $this->checkAttribute($name);
        $this->$name = $value;
        return $this->save();
    }

    // --------------------------------------------------------------
    // Support for model reload
    // --------------------------------------------------------------
    public function reload() {
        if (!$this->id) {
            throw new TipyModelException("Unable to reload unsaved model");
        }
        if ($this->isDeletedRecord) {
            throw new TipyModelException("Unable to reload deleted model");
        }
        $reloadedModel = $this->load($this->id);
        $this->data = $reloadedModel->data;
        $this->associationsCache = [];
    }

    // --------------------------------------------------------------
    // Creates new record from atributes immedialtely
    // --------------------------------------------------------------
    public static function create($attr = null) {
        $className = get_called_class();
        $instance = new $className($attr);
        $instance->save();
        return $instance;
    }

    // --------------------------------------------------------------
    // Create new row from model
    // --------------------------------------------------------------
    protected function createNewRecord() {
        $this->beforeCreate();
        $fields = [];
        $questions = [];
        $values = [];
        foreach ($this->reflections as $field => $attr) {
            // No need to create id
            // Skip attrs that doesn't set
            if ($field != "id" && array_key_exists($attr, $this->data)) {
                $fields[] = $field;
                $questions[] = "?";
                $values[] = $this->data[$attr];
            }
        }
        $fieldList = implode(",", $fields);
        $questions = implode(",", $questions);
        $query = "insert into ".$this->table."(".$fieldList.") values (".$questions.")";
        $result = $this->query($query, $values);
        $this->id = $this->lastInsertId();
        $this->afterCreate();
        return $result;
    }

    // --------------------------------------------------------------
    // Updates model's correspondent row
    // --------------------------------------------------------------
    protected function updateRecord() {
        if (!$this->id) {
            throw new TipyModelException("Cannot update record without an id");
        }
        $this->beforeUpdate();
        $query = "update ".$this->table." set ";
        $values = [];
        $updatePart = [];
        foreach ($this->reflections as $field => $attr) {
            // No need to update id
            // Skip attrs that doesn't set
            if ($field != "id" && array_key_exists($attr, $this->data)) {
                $updatePart[] = "$field=?";
                $values[] = $this->$attr;
            }
        }
        $query .= implode(", ", $updatePart)." where id = ?";
        $values[] = $this->id;
        $result = $this->query($query, $values);
        $this->afterUpdate();
        return $result;
    }

    // --------------------------------------------------------------
    // Delete model's correspondent method with assotiated records.
    // Usage:
    //     $post = BlogPost::load(123);
    //     $post->delete();

    // If method is called for new record exception is raised.
    //
    // After sucessfull delete oblect data is reset and object
    // become a new record
    // --------------------------------------------------------------

    public function delete() {
        if ($this->isNewRecord()) {
            throw new TipyModelException("Cannot delete unsaved model");
        }
        $this->beforeDelete();
        $result = $this->query("delete from ".$this->table." where id=?", [$this->id]);
        if ($this->hasOne) {
            foreach ($this->hasOne as $name => $properties) {
                if (array_key_exists('dependent', $properties)) {
                    if ($properties["dependent"]==='delete' && $this->$name) {
                        $this->$name->delete();
                    } elseif ($properties["dependent"]==='nullify' && $this->$name) {
                        $table = static::classNameToTableName($properties["class"]);
                        $key = array_key_exists('foreign_key', $properties) ? $properties["foreign_key"] : $this->tableForeignKeyFieldName($this->table);
                        $this->query('update '.$table.' set `'.$key.'` = null');
                    }
                }
            }
        }
        if ($this->hasMany) {
            foreach ($this->hasMany as $name => $properties) {
                foreach ($this->$name as $obj) {
                    if (array_key_exists('dependent', $properties)) {
                        if ($properties["dependent"]==='delete' && $obj) {
                            $obj->delete();
                        } elseif ($properties["dependent"]==='nullify' && $obj) {
                            $table = static::classNameToTableName($properties["class"]);
                            $key = $properties["foreign_key"] ? $properties["foreign_key"] : $this->tableForeignKeyFieldName($this->table);
                            $this->query('update '.$table.' set `'.$key.'` = null');
                        }
                    }
                }
            }
        }
        $this->isDeletedRecord = true;
        $this->associationsCache = [];
        $result = true;
        $this->afterDelete();
        return $result;
    }

    // --------------------------------------------------------------
    // Usage:
    //     $post = BlogPost::count([
    //          'conditions' => "title =?",
    //          'values' => ['Hello']
    //     ]);
    // --------------------------------------------------------------
    public static function count($options = []) {
        $className = get_called_class();
        $instance = new $className;
        $sql = "select count(id) as quantity from ".$instance->table;
        $where = "";
        if (array_key_exists('conditions', $options)) {
            $where = " where ".$options["conditions"];
        }
        $sql = $sql.$where;
        if (!isset($options['values'])) {
            $options['values'] = [];
        }
        $result = $instance->queryRow($sql, $options["values"]);
        return $result['quantity'];
    }

    // --------------------------------------------------------------
    // Usage:
    //     $post = BlogPost::find([
    //          'conditions' => "title =?",
    //          'values' => ['Hello'],
    //          'limit' => 2.
    //          'offset' => 3,
    //          'order' => 'user_id asc'
    //     ]);
    // --------------------------------------------------------------
    public static function find($options = ['values' => []]) {
        $className = get_called_class();
        $instance = new $className;
        $sql = "select * from ".$instance->table;
        $where = "";
        $order = "";
        if (array_key_exists('conditions', $options)) {
            $where = " where ".$options["conditions"];
        }
        $order = " order by ".(isset($options["order"]) ? $options["order"] : "id");
        $sql = $sql.$where.$order;
        if (!array_key_exists('values', $options)) {
            $options["values"] = [];
        }
        if (array_key_exists('limit', $options)) {
            if (!array_key_exists('offset', $options)) {
                $options["offset"] = 0;
            }
            $result =  $instance->limitQueryAllRows($sql, $options["offset"], $options["limit"], $options["values"]);
        } else {
            $result =  $instance->queryAllRows($sql, $options["values"]);
        }
        $instances = [];
        foreach ($result as $record) {
            $instance = new $className;
            $instance = self::instanceFromResult($instance, $record);
            $instances[] = $instance;
        }
        return $instances;
    }

    // --------------------------------------------------------------
    // Usage: the same as find but returns only one record
    // --------------------------------------------------------------
    public static function findFirst($options = []) {
        $options["limit"] = 1;
        $result = self::find($options);
        if (sizeof($result) > 0) {
            return $result[0];
        } else {
            return null;
        }
    }

    // --------------------------------------------------------------
    // Model validation. See example below
    // --------------------------------------------------------------
    public function validate() {
        // This is an example of how to check for title existence
        // if (!this->title) throw new TipyValidtionException('Title is mandatory');
    }

    // ---------------------------------------------------------------
    //  Protected
    // ---------------------------------------------------------------
    protected static function instanceFromResult($instance, $result) {
        foreach ($instance->reflections as $field => $attr) {
            $instance->data[$attr] = array_key_exists($field, $result) ? $result[$field] : null;
        }
        return $instance;
    }

    protected function findHasMany($name, $options = null) {
        $cacheAssoc = false;
        if (!$options and isset($this->associationsCache[$name])) {
            return $this->associationsCache[$name];
        } elseif (!$options) {
            $cacheAssoc = true;
            $options = [];
        }
        if (!isset($options["values"])) {
            $options["values"] = [];
        }
        $assocClass = $this->hasMany[$name]["class"];
        $parentKey = array_key_exists('foreign_key', $this->hasMany[$name]) ? $this->hasMany[$name]["foreign_key"] : $this->tableForeignKeyFieldName($this->table);
        $conditions = "$parentKey=?";
        if (isset($this->hasMany[$name]["conditions"])) {
            $conditions = $conditions." and (".$this->hasMany[$name]["conditions"].")";
        }
        if (isset($options["conditions"])) {
            $options["conditions"] = "(".$options["conditions"].") and ".$conditions;
        } else {
            $options["conditions"] = $conditions;
        }
        $options["values"][] = $this->id;
        if (array_key_exists('values', $this->hasMany[$name])) {
            $options["values"][] = $this->hasMany[$name]["values"];
        }
        $result = call_user_func($assocClass .'::find', $options);
        if ($cacheAssoc) {
            $this->associationsCache[$name] = $result;
        }
        return $result;
    }

    protected function findHasOne($name) {
        if (isset($this->associationsCache[$name])) {
            return $this->associationsCache[$name];
        }
        $assocClass = $this->hasOne[$name]["class"];
        $parentKey = array_key_exists('foreign_key', $this->hasOne[$name]) ? $this->hasOne[$name]["foreign_key"] : $this->tableForeignKeyFieldName($this->table);
        $conditions = "$parentKey=?";
        $options = ["conditions" => $conditions, "values" => [$this->id]];
        $assoc = call_user_func($assocClass .'::findFirst', $options);
        $this->associationsCache[$name] = $assoc;
        return $assoc;
    }

    protected function findBelongsTo($name) {
        if (isset($this->associationsCache[$name])) {
            return $this->associationsCache[$name];
        }
        $assocClass = $this->belongsTo[$name]["class"];
        $attr = array_key_exists('foreign_key', $this->belongsTo[$name]) ? $this->fieldNameToAttrName($this->belongsTo[$name]["foreign_key"]) : $this->classForeignKeyAttr($assocClass);
        $assoc = call_user_func($assocClass .'::load', $this->$attr);
        $this->associationsCache[$name] = $assoc;
        return $assoc;
    }

    protected function findHasManyThrough($name, $options = null) {
        $cacheAssoc = false;
        if (!$options and array_key_exists($name, $this->associationsCache)) {
            return $this->associationsCache[$name];
        } elseif (!$options) {
            $cacheAssoc = true;
        }
        $throughClass = $this->hasManyThrough[$name]["through"];
        $parentKey = array_key_exists('foreign_key', $this->hasManyThrough[$name]) ? $this->hasManyThrough[$name]["foreign_key"] : $this->tableForeignKeyFieldName($this->table);
        $throughOptions = [
            "conditions" => "$parentKey=?",
            "values"     => [$this->id]
        ];
        $result = call_user_func($throughClass .'::find', $throughOptions);
        if (!$result) {
            return [];
        }
        $ids = [];
        $targetKey = array_key_exists('through_key', $this->hasManyThrough[$name]) ? $this->fieldNameToAttrName($this->hasManyThrough[$name]["through_key"]) : $this->classForeignKeyAttr($this->hasManyThrough[$name]["class"]);
        foreach ($result as $row) {
            $ids[] = "'" . $row->$targetKey . "'";
        }
        $conditions = "id in (".implode(', ', $ids).")";
        if (!$options) {
            $options = [];
        }
        if (!isset($options["values"])) {
            $options["values"] = [];
        }
        $assocClass = $this->hasManyThrough[$name]["class"];

        if (isset($options["conditions"])) {
            $options["conditions"] = "(".$options["conditions"].") and ".$conditions;
        } else {
            $options["conditions"] = $conditions;
        }

        $result = call_user_func($assocClass .'::find', $options);
        if ($cacheAssoc) {
            $this->associationsCache[$name] = $result;
        }
        return $result;
    }

    // ----------------------------------------------------
    // Lock record for update
    // Works only within transaction!
    // ----------------------------------------------------
    public function lockForUpdate() {
        if (!$this->isTransactionInProgress()) {
            throw new TipyDaoException('No any transaction in progress');
        }
        return $this->query('select id from '.$this->table.' where id=? for update', [$this->id]);
    }

    // Hooks for Create, Update and Save

    // --------------------------------------------------------------
    // Default actions that are to be executed by default
    // before deletion
    // --------------------------------------------------------------
    public function beforeCreate() {
        // override this
    }

    // --------------------------------------------------------------
    // Default actions that are to be executed by default
    // after Create
    // --------------------------------------------------------------
    public function afterCreate() {
        // override this
    }

    // --------------------------------------------------------------
    // Default actions that are to be executed by default
    // before deletion
    // --------------------------------------------------------------
    public function beforeUpdate() {
        // override this
    }

    // --------------------------------------------------------------
    // Default actions that are to be executed by default
    // after Update
    // --------------------------------------------------------------
    public function afterUpdate() {
        // override this
    }

    // --------------------------------------------------------------
    // Default actions that are to be executed by default
    // before delete
    // --------------------------------------------------------------
    public function beforeDelete() {
        // override this
    }

    // --------------------------------------------------------------
    // Default actions that are to be executed by default
    // after deletion
    // --------------------------------------------------------------
    public function afterDelete() {
        // override this
    }

// ------------------------------------------------------------------
//  Methods for prefered naming conventions
// ------------------------------------------------------------------

    protected static function classNameToTableName($className) {
        $className = TipyInflector::pluralize($className);
        return TipyInflector::underscore($className);
    }

    protected static function fieldNameToAttrName($fieldName) {
        return TipyInflector::camelCase($fieldName);
    }

    protected static function tableForeignKeyFieldName($tableName) {
        return TipyInflector::singularize($tableName)."_id";
    }

    protected static function classForeignKeyAttr($className) {
        return lcfirst($className)."Id";
    }

    // time format for DB
    protected static function getCurrentTime() {
        return time();
    }
}

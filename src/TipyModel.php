<?php
/**
 * TipyModel
 *
 * @package tipy
 */

/**
 * Thown on TipyModel errors like "Unknown property", "Unknown metod", etc...
 */
class TipyModelException extends Exception {}

/**
 * Thow this from TipyModel::validate() method on validation errors
 */
class TipyValidationException extends Exception {}

/**
 * M in MVC. TipyModel is ORM connecting objects to database
 *
 * TipyModel:
 *
 * - Represents row in a table
 * - Defines associations and hierarchies between models
 * - Validates models before they get persisted to the database
 * - Performs database operations in an object-oriented fashion
 *
 * ## Conventions
 *
 * TipyModel follows "Convention over Configuration" paradigm and tries to use as
 * little configuration as possible. Of course you can configure model-database mapping
 * in the way you wish but you will write your code much faster following TipyModel
 * conventions:
 *
 * - <b>Class Name</b> - singular, CamelCase, first letter in upper case. - **BlogPost**
 * - <b>Table Name</b> - plural, snake_case, all letters in lower case    - **blog_posts**
 * - <b>Model Properties</b> - camelCase, first letter in lower case      - **createdAt**
 * - <b>Table Fields</b> - snake_case, all letters in lower case          - **created_at**
 * - <b>Foreign Key</b> - foreign table name + "_id"                      - **author_id**
 * - <b>Primary Key</b> - is always **id**
 * - If your table has **created_at** and **updated_at** fields they will be handled
 *   automatically
 *
 * These conventions can be changed by overriding TipyModel methods:
 * {@link classNameToTableName()},
 * {@link fieldNameToAttrName()},
 * {@link tableForeignKeyFieldName()},
 * {@link classForeignKeyAttr()},
 * and constants:
 * {@link CREATED_AT},
 * {@link UPDATED_AT}
 *
 * ## Defining Models
 *
 * Let's say you have the following table
 *
 * <code>
 * create table users (
 *     id int(11),
 *     first_name varchar(255),
 *     last_name varchar(255),
 *     primary key (id)
 * );
 * </code>
 *
 * To make TipyModel from this table you simply need to extend TipyModel class
 *
 * <code>
 * class User extends TipyModel {
 * }
 * </code>
 *
 * This magically connect User class to users table (see Conventions section above)
 * gives User class a lot of useful methods and magic properties to access table
 * fields
 *
 * <code>
 * // create new User object and save it to database
 * $user = new User();
 * $user->firstName = 'John';
 * $user->lastName = 'Doe';
 * $user->save();
 *
 * // or like this
 * $user = User::create([
 *     'firstName' => 'John',
 *     'lastName'  => 'Doe'
 * ]);
 *
 * $id = $user->id;
 * $sameUser = User::load($id);
 * echo $sameUser->firstName;
 * </code>
 *
 * ## Validation
 *
 * Model-level validation is the best way to ensure that only valid data is saved
 * into database. It cannot be bypassed by end users and is convenient to test and maintain.
 *
 * Validations are run autmatically before {@link save()} and {@link update()} send
 * SQL INSERT or UPDATE queries to the database.
 *
 * To add validation to your model simply override {@link validate()} method.
 *
 * <code>
 * class User extends TipyModel {
 *    public function validate() {
 *        if (!$this->firstName) throw new TipyValidtionException('First name should not be blank!');
 *        if (!$this->lastName) throw new TipyValidtionException('Last name should not be blank!');
 *    }
 * }
 * </code>
 *
 * The common way to fail validation is to throw {@link TipyValidtionException} and then to
 * catch it in controller.
 *
 * ## Hooks
 *
 * TipyModel allows to define logic triggered before or after an alteration of the model state.
 * To do this override the following methods in your model:
 *
 * - {@link $beforeCreate()}
 * - {@link $afterCreate()}
 * - {@link $beforeUpdate()}
 * - {@link $afterUpdate()}
 * - {@link $beforeDelete()}
 * - {@link $afterDelete()}
 *
 * ## Associations
 *
 * Association is a connection between two models. By declaring associations you
 * define Primary Key-Foreign Key connection between instances of the two models,
 * and you also get a number of utility methods added to your model. Tipy supports
 * the following types of associations:
 *
 * - {@link $hasMany}
 * - {@link $hasOne}
 * - {@link $belongsTo}
 * - {@link $hasManyThrough}
 *
 * To define define model associations you need to assign values to these properties.
 * <code>
 * class User extends TipyModel {
 *    protected $hasMany = ['posts', 'comments'];
 * }
 * </code>
 *
 * Association class name is evaluated automatically by {@link TipyInflector->classify} inflection.
 * If you wan't to specify class name different from association name you can pass association 
 * options as arrays:
 *
 * <code>
 * class User extends TipyModel {
 *    protected $hasMany = [
 *        'posts' => ['class' => 'BlogPost'],
 *        'comments' => ['class' => 'BlogComment']
 *    ];
 * }
 * </code>
 *
 * ### hasMany
 *
 * A hasMany association indicates a one-to-many connection with another model.
 *
 * <code>
 * create table users (               create table blog_posts (
 *     id int(11), <---------------+      id int(11),
 *     first_name varchar(255),    |      title varchar(255),
 *     last_name varchar(255),     |      body text,
 *     primary key (id)            +----- user_id int(11),
 * );                                     primary key (id)
 *                                    );
 * </code>
 *
 * <code>
 * class User extends TipyModel {
 *    protected $hasMany = [
 *        'posts' => ['class' => 'BlogPost']
 *    );
 * }
 * </code>
 *
 * This gives User model magic property User::posts
 *
 * <code>
 * $posts = $user->posts;
 * </code>
 *
 * ### hasOne
 *
 * A hasOne association indicates a one-to-one connection with another model.
 *
 * <code>
 * create table users (               create table accounts (
 *     id int(11), <---------------+      id int(11),
 *     first_name varchar(255),    |      cc_number varchar(20),
 *     last_name varchar(255),     |      cc_expire_date varchar(5),
 *     primary key (id)            +----- user_id int(11),
 * );                                     primary key (id)
 *                                    );
 * </code>
 *
 * <code>
 * class User extends TipyModel {
 *    protected $hasOne = ['account'];
 * }
 * </code>
 *
 * This gives User model magic property User::account
 *
 * <code>
 * $ccNumber = $user->account->ccNumber;
 * </code>
 *
 * ### belongsTo
 *
 * A belongsTo association is an opposite to hasMany and hasOne
 *
 * <code>
 * create table users (               create table blog_posts (
 *     id int(11), <---------------+      id int(11),
 *     first_name varchar(255),    |      title varchar(255),
 *     last_name varchar(255),     |      body text,
 *     primary key (id)            +----- user_id int(11),
 * );                                     primary key (id)
 *                                    );
 * </code>
 *
 * <code>
 * class BlogPost extends TipyModel {
 *    protected $belongsTo = ['user'];
 * }
 * </code>
 *
 * This gives BlogPost model magic property BlogPost->user
 *
 * <code>
 * $firstName = $post->user->firstName;
 * </code>
 *
 * ### hasManyThrough
 *
 * A hasManyThrough association indicates a many-to-many connection with another model
 * through a third model.
 *
 * <code>
 * create table users (
 *     id int(11), <-------------+
 *     first_name varchar(255),  |   create table memberships (
 *     last_name varchar(255)    +------ user_id int(11).
 * );                                    id int(11),
 *                               +------ group_id int(11)
 * create table groups (         |   );
 *     id int(11), <-------------+
 *     name varchar(255)
 * );
 * </code>
 *
 * <code>
 * class User extends TipyModel {
 *    protected $hasManyThrough = [
 *        'groups' => ['class' => 'Group', 'through' => 'Membership'],
 *    );
 * }
 * </code>
 *
 * This gives User model magic property User::groups
 *
 * <code>
 * $groups = $user->groups;
 * </code>
 *

 *
 * ### Association Options
 *
 * Association options are arrays with the following keys
 *
 * - **'class'** - associated model class
 * - **'dependent'** - 'delete', 'nullify', or null -
 *   What to do with associated model rows when this model row is deleted
 * - **'conditions'** - conditions to select associated records in addition
 *   to foreign_key.
 * - **'values'** - values for conditions
 * - **'foreign_key'** - custom associated record foreign key
 * - **'through_key'** - custom second foreign key for $hasManyThrough
 *
 * <code>
 * class User extends TipyModel {
 *    protected $hasMany = [
 *        'messages' => [
 *            'class' => 'Message',
 *            'dependent' => 'delete'
 *        ],
 *        // 7 days old messages
 *        'oldMessages' => [
 *            'class' => 'Message',
 *            'conditions' => 'created_at < ?',
 *            'values' => [strtotime('-1 week')]
 *        ]
 *    ];
 * }
 * </code>
 *
 * ### Associations Cache
 *
 * Associations are cached. So if you call
 * <code>
 * $post->comments
 * </code>
 * more than once only one query will be executed (first call) and then
 * comments will always be taken from cache.
 *
 * Downside of this approach: Cache doesn't know if comments were deleted
 * or modified in the database. To reset associations cache use {@link TipyModel::reload()}
 *
 * Associations with conditions are not cached. This means that
 * <code>
 * $post->comments(['order' => 'created_at desc'])
 * </code>
 * will allways execute query
 *
 * In short always look for parethesis:
 * <code>
 * $post->comments; // is cached
 * $post->comments(...); // is not cached
 * </code>
 *
 * @todo Accept conditions and values in one array 'conditions' => ['id = ?', $id]
 * @todo Simple way to specify table name in model class
 * @todo Improve find() to accept arguments like Post::find('first', 'conditions' => ['created_at > ?', time()]);
 */
class TipyModel extends TipyDAO {

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Rules to cast MySQL types to PHP types
     * @var array
     */
    protected static $mysqlToPhpTypes = [
        'char'      => 'string',
        'varchar'   => 'string',
        'binary'    => 'string',
        'varbinary' => 'string',
        'blob'      => 'string',
        'text'      => 'string',
        'enum'      => 'string',
        'set'       => 'string',
        'integer'   => 'integer',
        'int'       => 'integer',
        'smallint'  => 'integer',
        'tinyint'   => 'integer',
        'mediumint' => 'integer',
        'bigint'    => 'integer',
        'bit'       => 'integer',
        'boolean'   => 'integer',
        'decimal'   => 'float',
        'numeric'   => 'float',
        'double'    => 'float',
        'date'      => 'datetime',
        'datetime'  => 'datetime',
        'timestamp' => 'datetime'
    ];

    /**
     * Global models<->tables reflections cache
     *
     * One for all models
     * @var array
     */
    protected static $globalReflections = [];

    /**
     * Model class name
     *
     * Just to call get_class only once
     * @var string
     */
    public $className;

    /**
     * Table name
     * @var string
     */
    public $table;

    /**
     * Magic properties to access row columns
     *
     * filled automatically
     * @var array
     */
    public $attributes;

    /**
     * Table column names list
     *
     * fields autmatically
     * @var array
     */
    public $fields;

    /**
     * Table column types list
     *
     * filled autmatically
     * @var array
     */
    public $fieldTypes;

    /**
     * column => property reflections
     *
     * Associative array with connections betweed table fields and
     * model's magic propertiesa
     * @var array
     */
    public $reflections;


    /**
     * Magic properties values
     *
     * @var array
     */
    public $data;

    /**
     * True if table row represented by model is deleted
     * @var boolean
     */
    public $isDeletedRecord;

    /**
     * Associations cache
     * @var array
     */
    public $associationsCache;

    /**
     * One-to-many associations
     * @var array
     */
    protected $hasMany;

    /**
     * One-to-one associations
     * @var array
     */
    protected $hasOne;

    /**
     * Many-to-one associations
     * @var array
     */
    protected $belongsTo;

    /**
     * Many-to-many associations through a third models
     * @var array
     */
    protected $hasManyThrough;

    /**
     * Create new model
     *
     * <code>
     * $post = new BloPost;
     *
     * $post = new BloPost([
     *     'title' => 'Hello',
     *     'body' => 'World!'
     * ]);
     * </code>
     *
     * **NOTE:** This method does not save anything to database
     *
     * - New model is not saved to the database and has $this->isNewRecord() == true
     * - If $attrs has **'id'** key then model with the same id will be loaded from
     *   the database and its attributes will be overwritten (but not saved)
     *
     * @param array $attrs Associative array representing new model properies
     */
    public function __construct($attrs = null) {
        parent::__construct();
        $this->className = get_class($this);
        $this->table = $this->classNameToTableName($this->className);
        $this->makeReflection();
        $this->isDeletedRecord = false;
        $this->checkAssociationClasses();
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

    /**
     * Output something on *echo $obj;*
     */
    public function __toString() {
        return '<'.$this->className.'>#'.$this->id;
    }

    /**
     * Magic method to assign model property
     *
     * @param string $name
     * @param mixed $value
     * @throws TipyModelException if model property is not defined
     */
    public function __set($name, $value) {
        $this->checkAttribute($name);
        $this->data[$name] = $value;
    }

    /**
     * Magic method to get the model property value or to execute model method
     *
     * Lookup order:
     *
     * - model methods
     * - model assosications
     * - model properties
     *
     * @param string $name
     * @throws TipyModelException on lookup failure
     */
    public function __get($name) {
        if (method_exists($this, $name) and is_callable([$this, $name])) {
            return call_user_func([$this, $name]);
        }
        if ($this->belongsTo && array_key_exists($name, $this->belongsTo)) {
            return $this->findBelongsTo($name);
        }
        if ($this->hasMany && array_key_exists($name, $this->hasMany)) {
            return $this->findHasMany($name);
        }
        if ($this->hasOne && array_key_exists($name, $this->hasOne)) {
            return $this->findHasOne($name);
        }
        if ($this->hasManyThrough && array_key_exists($name, $this->hasManyThrough)) {
            return $this->findHasManyThrough($name);
        }
        $this->checkAttribute($name);
        return array_key_exists($name, $this->data) ? $this->data[$name] : null;
    }

    /**
     * Magic method to call model property as a function
     *
     * Use it to pass conditions to assiciation properties
     *
     * <code>
     * $post->comments(['conditions' => 'created_at > ?', 'values' => strtotime('-1 day')])
     * </code>
     *
     * @param string $name
     * @param array $args
     * @throws TipyModelException on lookup failure
     */
    public function __call($name, $args) {
        if (isset($this->hasMany[$name])) {
            return $this->findHasMany($name, $args[0]);
        }
        if (isset($this->hasManyThrough[$name])) {
            return $this->findHasManyThrough($name, $args[0]);
        }
        throw new TipyModelException("Unknown method '".$name."' for ".$this->className);
    }

    /**
     * Check for model property existance
     *
     * @throws TipyModelException if property does not exist
     */
    public function checkAttribute($name) {
        if (!in_array($name, $this->attributes)) {
            throw new TipyModelException("Unknown property '".$name."' for ".$this->className);
        }
    }

    /**
     * Reflect database table to model
     *
     * Loads table columns and creates model properties from them
     */
    protected function makeReflection() {
        $this->data = [];
        if (array_key_exists($this->className, self::$globalReflections)) {
            $this->fields = self::$globalReflections[$this->className]["fields"];
            $this->fieldTypes = self::$globalReflections[$this->className]["fieldTypes"];
            $this->attributes = self::$globalReflections[$this->className]["attributes"];
            $this->reflections = self::$globalReflections[$this->className]["reflections"];
        } else {
            $this->fields = [];
            $this->attributes = [];
            $this->reflections = [];
            $fields = $this->queryAllRows("show columns from ".$this->table);
            foreach ($fields as $field) {
                $fieldName = $field["Field"];
                $fieldType = preg_replace('/\(.*\)/', '', $field["Type"]);
                $fieldType = preg_replace('/ unsigned/', '', $fieldType);
                $attrName = $this->fieldNameToAttrName($fieldName);
                $this->fields[] = $fieldName;
                $this->fieldTypes[$fieldName] = $fieldType;
                $this->attributes[] = $attrName;
                $this->reflections[$fieldName] = $attrName;
            }
            // Store reflections for future use of this class
            self::$globalReflections[$this->className] = [];
            self::$globalReflections[$this->className]["fields"] = $this->fields;
            self::$globalReflections[$this->className]["fieldTypes"] = $this->fieldTypes;
            self::$globalReflections[$this->className]["attributes"] = $this->attributes;
            self::$globalReflections[$this->className]["reflections"] = $this->reflections;
        }
    }

    /**
     * Creale model instance from the table row
     *
     * <code>
     * $post = BlogPost::load(123);
     * </code>
     *
     * @param integer $id
     * @return TipyModel|null
     */
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

    /**
     * True if new model instance has not been saved yet
     *
     * @return boolean
     */
    public function isNewRecord() {
        return !$this->id;
    }

    /**
     * Save model data as a table row
     *
     * @throws TipyModelException if model is marked as deleted
     * @return mysqli_result
     */
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

    /**
     * Set model property and update model row immediately
     *
     * @param string $name
     * @param mixed $value
     * @throws TipyModelException if property does not exist
     */
    public function update($name, $value) {
        $this->checkAttribute($name);
        $this->$name = $value;
        return $this->save();
    }

    /**
     * Reload model from the database
     *
     * @throws TipyModelException if trying to reload new (not-saved) model
     * @throws TipyModelException if trying to reload model marked as deleted
     */
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

    /**
     * Create new model, save it to the database, and return model instance
     *
     * <code>
     * $post = BlogPost::create([
     *     'title' => 'Hello World'
     * ])
     *
     * // is equivalent of
     *
     * $post = new BlogPost;
     * $post->title = 'Hello World';
     * $post->save();
     * </code>
     *
     * @param array $attrs
     * @return TipyModel
     */
    public static function create($attr = null) {
        $className = get_called_class();
        $instance = new $className($attr);
        $instance->save();
        return $instance;
    }

    /**
     * Save new model as a table row
     *
     * @return mysqli_result
     */
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

    /**
     * Updates table row connected to model
     *
     * @return mysqli_result
     */
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

    /**
     * Delete table row connected to model
     *
     * - deletes table row
     * - deletes all rows for associations with **'dependent' => 'delete'**
     * - set to null all foreign keys for associations with **'dependent' => 'nullify'**
     * - marks model as deleted
     *
     * @return mysqli_result
     */
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

    /**
     * Count model records by conditions
     *
     * <code>
     * $post = BlogPost::count([
     *      'conditions' => "user_id = ?",
     *      'values' => [42]
     * ]);
     * </code>
     *
     * @return integer
     */
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
        return (int) $result['quantity'];
    }

    /**
     * Return array of models by conditions
     *
     * <code>
     * $post = BlogPost::find([
     *      'conditions' => "title =?",
     *      'values' => ['Hello'],
     *      'limit' => 2.
     *      'offset' => 3,
     *      'order' => 'user_id asc'
     * ]);
     * </code>
     *
     * @param array $options
     * @return array
     */
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

    /**
     * Same as find but return only first instance
     *
     * <code>
     * $post = BlogPost::findFirst([
     *      'conditions' => "title =?",
     *      'values' => ['Hello'],
     *      'limit' => 2.
     *      'offset' => 3,
     *      'order' => 'user_id asc'
     * ]);
     * </code>
     *
     * @return TipyModel
     */
    public static function findFirst($options = []) {
        $options["limit"] = 1;
        $result = self::find($options);
        if (sizeof($result) > 0) {
            return $result[0];
        } else {
            return null;
        }
    }

    /**
     * Validates model data
     *
     * Executed before SQL INSERT or UPDATE statements are sent to database.
     * Override this in your model classes
     */
    public function validate() {
    }


    /**
     * Fill model instance with data from mysqli_result
     *
     * @param TipyModel $instance
     * @param mysqli_result
     * @return TipyModel
     */
    protected static function instanceFromResult($instance, $result) {
        foreach ($instance->reflections as $field => $attr) {
            if (array_key_exists($field, $result) && $result[$field] !== null) {
                $instance->data[$attr] = $instance->typeCast($field, $result[$field]);
            } else {
                $instance->data[$attr] = null;
            }
        }
        return $instance;
    }

    /**
     * Cast column value to PHP type
     *
     * MySQL returns all field values as strings.
     * This method return value casted to PHP type
     * using rules from {@link $mysqlToPhpTypes}
     *
     * @param string field
     * @param mixed $value
     * @return mixed
     */
    protected function typeCast($field, $value) {
        $type = $this->fieldTypes[$field];
        switch (self::$mysqlToPhpTypes[$type]) {
            // $value is already string so we don't typecast to string
            case 'integer':
                settype($value, 'integer');
                break;
            case 'float':
                settype($value, 'float');
                break;
            case 'datetime':
                $value = new DateTime($value);
                break;
        }
        return $value;
    }


    /**
     * Check and fill missing association classes
     * Recognize class names from association names
     * This allows to define assiciations by specifying
     * its name only
     * <code>
     *  $hasMany = ['comments', 'posts'];
     * </cde>
     */
     private function checkAssociationClasses() {
        foreach(['hasMany', 'hasOne', 'belongsTo', 'hasManyThrough'] as $assocType) {
            if (!$this->$assocType) {
                continue;
            }
            if (!is_array($this->$assocType)) {
                throw new TipyModelException("Associations definition should be an Array");
            }
            $assocArray = $this->$assocType;
            foreach($assocArray as $name => $options) {
                // If $name is integer then this is assoc specified without options
                // Make it array for future usage
                if (is_int($name)) {
                    // Transform ['post'] array to ['post' => []]
                    unset($assocArray[$name]);
                    $name = $options;
                    $assocArray[$name] = [];
                }
                if (!array_key_exists('class', $assocArray[$name])) {
                    $assocArray[$name]['class'] = TipyInflector::classify($name);
                }
            }
            $this->$assocType = $assocArray;
        }
     }

    /**
     * Select from the database and instantiaate {@link $hasMany}
     * associated models by name and conditions
     *
     * @internal
     * @param string name
     * @param array $options
     * @return array
     */
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

    /**
     * Select from the database and instantiate {@link $hasOne}
     * associated model by name
     *
     * @internal
     * @param string name
     * @return TipyModel
     */
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

    /**
     * Select from the database and instantiate {@link $belongsTo}
     * associated model by name
     *
     * @internal
     * @param string name
     * @return TipyModel
     */
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

    /**
     * Select from the database and instantiate {@link $hasManyThrough}
     * associated model by name and conditions
     *
     * @internal
     * @param string name
     * @param array $options
     * @return array
     */
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

    /**
     * Lock model's connected row for update
     *
     * Should be called inside {@link transaction()}
     *
     * @throws TipyDaoException if called outside transaction
     */
    public function lockForUpdate() {
        if (!$this->isTransactionInProgress()) {
            throw new TipyDaoException('No any transaction in progress');
        }
        return $this->query('select id from '.$this->table.' where id=? for update', [$this->id]);
    }

    // ----------------------------------------
    // Hooks
    // ----------------------------------------

    /**
     * TipyModel hook called before {@link create()} and {@link createNewRecord}
     *
     * Override this in your model to add logic
     */
    public function beforeCreate() {
    }

    /**
     * TipyModel hook called after {@link update()} and {@link updateRecord}
     *
     * Override this in your model to add logic
     */
    public function afterCreate() {
    }

    /**
     * TipyModel hook called before {@link update()} and {@link createNewRecord}
     *
     * Override this in your model to add logic
     */
    public function beforeUpdate() {
    }

    /**
     * TipyModel hook called after {@link update()} and {@link createNewRecord}
     *
     * Override this in your model to add logic
     */
    public function afterUpdate() {
    }

    /**
     * TipyModel hook called before {@link delete()}
     *
     * Override this in your model to add logic
     */
    public function beforeDelete() {
    }

    /**
     * TipyModel hook called after {@link delete()}
     *
     * Override this in your model to add logic
     */
    public function afterDelete() {
    }

    /**
     * ------------------------------------------------------
     * Conventions Definition
     * Override methods below to change TipyModel conventions
     * ------------------------------------------------------
     */

    /**
     * Converts table name to model class name
     *
     * You may override this method to change the rules
     *
     * @param string $className
     * @return string
     */
    protected static function classNameToTableName($className) {
        return TipyInflector::tableize($className);
    }

    /**
     * Converts table column name to model property name
     *
     * You may override this method to change the rules
     *
     * @param string $fieldName
     * @return string
     */
    protected static function fieldNameToAttrName($fieldName) {
        return TipyInflector::camelCase($fieldName);
    }

    /**
     * Return foreign key name for foreign table
     *
     * You may override this method to change the rules
     *
     * @param string $tableName
     * @return string
     */
    protected static function tableForeignKeyFieldName($tableName) {
        return TipyInflector::singularize($tableName)."_id";
    }

    /**
     * Associated model property representing foreign key
     *
     * You may override this method to change the rules
     *
     * @param string $className
     * @return string
     */
    protected static function classForeignKeyAttr($className) {
        return lcfirst($className)."Id";
    }

    /**
     * Return current time for *created_at* and *updated_at* columns
     *
     * Default TipyModel convention is to use **unix timestamp** for
     * timestamps. You can change this by overriding this method
     * in your model
     *
     * @return integer
     */
    protected static function getCurrentTime() {
        return time();
    }
}

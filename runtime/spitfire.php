<?php
class RecordNotFoundException extends Exception {}
class InvalidRecordException extends Exception {}
class InvalidPropertyException extends Exception {}

class Errors implements IteratorAggregate
{
    private $errors = array();
    private $base   = array();
    
    public function ok() {
        return count($this->errors) == 0 && count($this->base) == 0;
    }
    
    public function add($key, $error) {
        $this->errors[$key][] = $error;
    }
    
    public function add_to_base($message) {
        $this->base[] = $message;
    }
    
    public function on($field) {
        return isset($this->errors[$field]) ? $this->errors[$field] : false;
    }
    
    public function first_on($field) {
        return isset($this->errors[$field]) ? $this->errors[$field][0] : false;
    }
    
    public function full_messages() {
        $messages = array();
        foreach ($this->errors as $field => $errors) {
            $field = Inflector::humanize($field);
            foreach ($errors as $message) {
                if ($message[0] == '^') {
                    $messages[] = substr($message, 1);
                } else {
                    $messages[] = "$field $message";
                }
            }
        }
        foreach ($this->base as $b) $messages[] = $b;
        return $messages;
    }
    
    public function getIterator() {
        return new ArrayIterator($this->full_messages());
    }
}

class SpitfireModel
{
    //
    // Static
    
    public static function table_name() {
        return null;
    }
    
    public static function db() {
        return GDB::instance();
    }
    
    public static function find() {
        $args = func_get_args();
        switch (func_num_args()) {
            case 1:
                return static::find_one($args[0]);
            default:
                throw new \Exception("unknown arguments to find()");
        }
    }
    
    public static function find_one($id) {
        
        $id = (int) $id;
        
        $target_class = get_called_class();
        $table = static::table_name();
        $sql = "SELECT * FROM $table WHERE id = $id";
        $res = static::db()->q($sql);
        
        $row = $res->row();
        if ($row === false) {
            throw new RecordNotFoundException("couldn't find record with ID $id");
        } else {
            // TODO: work out what class we really need based on STI stuff
            $instance = new $target_class;
            $instance->wakeup($row);
            
            return $instance;
        }
    
    }
    
    //
    // Instance
    
    protected $is_saved = false;
    
    public function __construct($properties = null) {
        if ($properties !== null) {
            $this->set_properties($properties);
        }
    }
    
    //
    // Persistence
    
    public function is_saved() {
        return $this->is_saved;
    }
    
    public function save_or_throw() {
        if (!$this->save()) throw new InvalidRecordException("can't invalid record");
        return true;
    }
    
    public function save($perform_validation = true) {
        
        if ($perform_validation && !$this->is_valid()) {
            return false;
        }
        
        $db = static::db();
        if ($this->is_saved()) {
            $db->update(static::table_name(), $this->quoted_attributes(), $this->primary_key());
        } else {
            $db->insert(static::table_name(), $this->quoted_attributes());
            $this->assign_serial($db->last_insert_id());
            $this->is_saved = true;
        }
        
        return true;
    
    }
    
    public function destroy() {
        if ($this->is_saved()) {
            $db = static::db();
            $db->delete(static::table_name(), $this->primary_key());
            $this->assign_serial(null);
            $this->is_saved = false;
        }
    }
    
    private function assign_serial($value) {
        if ($field = $this->serial_field()) {
            $this->$field = $value;
        }
    }
    
    // Will be overridden in generated code to return the name of auto-increment field,
    // if it exists.
    protected function serial_field() {
        return null;
    }
    
    /**
     * Instantiate a record after loading its attributes from a persistence store.
     * It's only valid to call this method immediately after construction, with an array
     * containing all attributes.
     *
     * @param object attributes
     */
    public function wakeup(array $row) {
        $this->set_attributes($row);
        $this->is_saved = true;
    }
    
    //
    // Attributes
    
    public function quoted_attributes() {
        return array();
    }
    
    // Sets all low-level object attributes
    // $attributes must contain the full set of attributes applicable to this object.
    // Although it's public, set_attributes() is not intended for general usage.
    // Most users will be more interested in set_properties()
    public function set_attributes(array $attributes) {}
    
    //
    // Properties
    
    public function set_properties(array $p, $with_protection = true) {
        foreach ($attributes as $k => $v) {
            if (method_exists($this, "set_$k")) {
                $this->{"set_{$k}"}($v);
            } else {
                throw new InvalidPropertyException("object has no property named '$k'");
            }
        }
    }
    
    //
    // Validation
    
    protected $errors = null;
    
    public function is_valid() {
        $this->errors = new Errors;
        $this->fire('before_validation');
        $this->run_macro_validations();
        $this->validate();
        $this->fire('after_validation');
        return $this->errors()->ok();
    }
    
    public function errors() {
        return $this->errors;
    }
    
    // This method will be overwritten automatically to run macro-based validations
    protected function run_macro_validations() {}
    
    // Override this method to perform custom validations
    protected function validate() {}
    
    
    
    
    
    //
    //
    
    
    
    
    public function primary_key() { return array('id' => null); }
    public function assign_primary_key() { }
    
    
    
    
}
?>
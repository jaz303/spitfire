<?php
namespace spitfire;

/**
 * An Attribute is a single, typed value that is persisted to the database.
 * Every attribute is backed by a protected or public instance variable.
 * At code generation time, a protected instance variable will be created if none exists.
 * 
 * You won't often deal with attributes directly, in fact it's actively discouraged.
 * Use a Property instead!
 *
 * Supported types: boolean, date, datetime, float, integer, serialize, string
 * Types that might be supported someday: time, anything else that fits into a single SQL field
 */
class Attribute
{
    private $model;
    private $name;
    private $type;
    
    public function __construct(Model $model, $name, $type) {
        $this->model = $model;
        $this->name  = $name;
        $this->type  = $type;
    }
    
    public function name() { return $this->name; }
    public function type() { return $this->type; }
    
    public function render_class_body() {
        if (!$this->model->get_reflection()->hasProperty($this->name)) {
            return "protected \${$this->name};\n";
        } else {
            return "";
        }
    }
    
    public function quoter($db) {
        if ($this->type == 'serialize') {
            return "\${$db}->quote_string(serialize(\$this->{$this->name}))";
        } else {
            return "\${$db}->{$this->quote_method()}(\$this->{$this->name})";
        }
    }
    
    public function assignment_from_array($array_name) {
        if ($this->type == 'serialize') {
            return "\$this->{$this->name()} = unserialize({$array_name}['{$this->name()}']);";
        } else {
            return "\$this->{$this->name()} = {$array_name}['{$this->name()}'];";
        }
    }
    
    public function quote_method() {
        switch ($this->type) {
            case 'boolean':     return 'quote_boolean';
            case 'date':        return 'quote_date';
            case 'datetime':    return 'quote_datetime';
            case 'float':       return 'quote_float';
            case 'integer':     return 'quote_integer';
            case 'serialize':   throw new Exception("internal error - serialize shouldn't get here!");
            case 'string':      return 'quote_string';
            default:            throw new Exception("unknown attribute type - {$this->type}");
        }
    }
}
?>
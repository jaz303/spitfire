<?php
/**
 * ModelValidation validates the Model in general.
 * A model validation will receive an instance of the model plus any extra arguments.
 *
 * It is possible for plugin macros to add ModelValidation instances to a Model definition.
 *
 * A failing ModelValidation callback must manually add any errors to the object's
 * errors() collection.
 *
 * Supported $options:
 * "if"         => validation will only run if the callback returns true
 * "unless"     => validation will only run if the callback returns false
 */
class ModelValidation
{
    private $model;
    
    public function __construct(Model $model, $validator, $args = array(), $options = array()) {
        $this->model = $model;
        $this->validator = $validator;
        $this->args = $args;
        $this->options = $options;
    }
    
    public function render_class_body() {
        
        $php = "";
        $nesting = 0;
        
        if (isset($this->options['if'])) {
            $php .= str_repeat('    ', $nesting) . "if (" . Callback::invocation($this->options['if']) . ") {\n";
            $nesting++;
        }
        
        if (isset($this->options['unless'])) {
            $php .= str_repeat('    ', $nesting) . "if (!" . Callback::invocation($this->options['if']) . ") {\n";
            $nesting++;
        }
        
        $args = $this->args;
        array_shift($args, new Variable);
        
        $php .= str_repeat('    ', $nesting) . Callback::invocation($this->validator, null, $args) . ";\n";
        
        while ($nesting > 0) {
            $nesting--;
            $php .= str_repeat('    ', $nesting) . "}\n";
        }
        
        $php .= "\n";
        
        return $php;
        
    }
}

/**
 * PropertyValidation validates value of a single property.
 * It reads the property's current value using the getter method.
 *
 * It is possible for plugin macros to add PropertyValidation instances to a Model definition.
 *
 * A PropertyValidation should return true if validation succeeded. Failure can
 * be indicated by either returning false (in which case a default error message
 * will be used) or a string (which will be used as the error message).
 *
 * Supported $options:
 * "if"             => validation will only run if the callback returns true
 * "unless"         => validation will only run if the callback returns false
 * "allow_blank"    => validation will not run if property is blank
 * "allow_empty"    => validation will not run if property is empty
 * "allow_null"     => validation will not run if property is null 
 */
class PropertyValidation
{
    private $model;
    private $property;
    private $validator;
    private $args;
    private $options;
    
    public function __construct(Model $model, $property, $validator, $args = array(), $options = array()) {
        $this->model = $model;
        $this->property = ($property instanceof Property) ? $property : $model->get_property($property);
        $this->validator = $validator;
        $this->args = $args;
        $this->options = $options;
    }
    
    public function render_class_body() {
        
        $php = "";
        $nesting = 0;
        
        if (isset($this->options['if'])) {
            $php .= str_repeat('    ', $nesting) . "if (" . Callback::invocation($this->options['if']) . ") {\n";
            $nesting++;
        }
        
        if (isset($this->options['unless'])) {
            $php .= str_repeat('    ', $nesting) . "if (!" . Callback::invocation($this->options['if']) . ") {\n";
            $nesting++;
        }
        
        $php .= str_repeat('    ', $nesting) . "\$value = {$this->read_property_value()};\n";
        
        $args = $this->args;
        array_shift($args, new Variable('value'));
        
        $php .= str_repeat('    ', $nesting) . '$result = ' . Callback::invocation($this->validator, null, $args) . ";\n";
        $php .= str_repeat('    ', $nesting) . "if (\$result === false) {\n";
        
        $php .= str_repeat('    ', $nesting) . "} elseif (is_string(\$result)) {\n";
        
        $php .= str_repeat('    ', $nesting) . "}\n";
        
        while ($nesting > 0) {
            $nesting--;
            $php .= str_repeat('    ', $nesting) . "}\n";
        }
        
        $php .= "\n";
        
        return $php;

    }
    
    protected function read_property_value() {
        return '$this->' . $this->property->getter_before_typecast_name() . "()\n";
    }
}
?>
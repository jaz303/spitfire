<?php
namespace spitfire;

class Model
{
    private $universe;                          // Universe to which model belongs
    private $klass;                             // Reflection
    private $class_name;                        // Fully-qualified classname
    private $annotations;                       // Class annotations
    
    private $attributes         = array();      // Model attributes
    private $properties         = array();      // Model properties
    private $serial             = null;         // Autonumber field
    private $validations        = array();      // Validations
    
    public function __construct(Universe $universe, \ReflectionClass $class) {
        
        $this->universe     = $universe;
        $this->klass        = $class;
        $this->class_name   = $class->getName();
        $this->annotations  = Annotation::parse($class);
        
        $this->scan_attributes();
        $this->scan_properties();
        $this->scan_serial();
        $this->scan_validations();
        
    }
    
    public function get_universe() { return $this->universe; }
    public function get_reflection() { return $this->klass; }
    public function get_class_name() { return $this->class_name; }
    public function get_annotations() { return $this->annotations; }
    
    public function render_class_body() {
        
        $php = "";
        
        //
        // Table name
        
        if (isset($this->annotations['table'])) {
            $php .= "public static function table_name() {\n";
            $php .= "    return " . var_export($this->annotations['table'], true) . ";\n";
            $php .= "}\n\n";
        }
        
        //
        // Attributes
        
        foreach ($this->attributes as $attribute) {
            $php .= $attribute->render_class_body();
        }
        
        $php .= "\n";
        $php .= "public function quoted_attributes() {\n";
        $php .= "    \$db = static::db();\n";
        $php .= "    return array_merge(parent::quoted_attributes(), array(\n";
        
        foreach ($this->attributes as $attribute) {
            $php .= "        '{$attribute->name()}' => " . $attribute->quoter('db') . ",\n";
        }
        
        $php .= "    ));\n";
        $php .= "}\n\n";
        
        $php .= "public function set_attributes(array \$attributes) {\n";
        $php .= "    parent::set_attributes(\$attributes);\n";
        
        foreach ($this->attributes as $attribute) {
            $php .= "    " . $attribute->assignment_from_array('$attributes') . "\n";
        }
        
        $php .= "}\n";
        
        
        //
        // Properties
        
        foreach ($this->properties as $property) {
            $php .= $property->render_class_body();
        }
        
        //
        // Serial
        
        if ($this->serial !== null) {
            if (!$this->klass->hasMethod("get_{$this->serial}")) {
                $php .= "public function get_{$this->serial}() {\n";
                $php .= "    \$this->{$this->serial};\n";
                $php .= "}\n\n";
            }
        }
        
        //
        // Validations
        
        if (count($this->validations)) {
            $php .= "public function run_macro_validations() {\n";
            $php .= "    parent::run_macro_validations();\n";
            foreach ($this->validations as $validation) {
                $php .= $validation->render_class_body();
            }
            $php .= "}\n\n";
        }
        
        //
        // All done
        
        return $php;

    }
    
    //
    // Attributes
    
    public function has_attribute($name) { return isset($this->attributes[$name]); }
    public function get_attribute($name) { return $this->attributes[$name]; }
    
    public function add_attribute(Attribute $attribute) {
        if (isset($this->attributes[$attribute->name()])) {
            throw new Exception("duplicate attribute {$attribute->name()}");
        }
        $this->attributes[$attribute->name()] = $attribute;
    }
    
    //
    // Properties
    
    public function has_property($name) { return isset($this->properties[$name]); }
    public function get_property($name) { return $this->properties[$name]; }
    
    public function add_property(Property $property) {
        if (isset($this->properties[$property->name()])) {
            throw new Exception("duplicate property {$property->name()}");
        }
        $this->properties[$property->name()] = $property;
    }
    
    //
    // Builder methods
    
    private function scan_attributes() {
        foreach ($this->klass->getProperties() as $ref) {
            $annotations = Annotation::parse($ref);
            if (isset($annotations['attribute'])) {
                $this->add_attribute(new Attribute($this,
                                                   $ref->getName(),
                                                   Utilities::calculate_type($ref)));
            }
        }
    }
    
    private function scan_properties() {
        foreach ($this->klass->getProperties() as $ref) {
            $annotations = Annotation::parse($ref);
            if (isset($annotations['property'])) {
                $this->add_property(new Property($this,
                                                 $ref->getName(),
                                                 Utilities::calculate_type($ref)));
            }
        }
    }
    
    private function scan_serial() {
        foreach ($this->klass->getProperties() as $ref) {
            $annotations = Annotation::parse($ref);
            if (isset($annotations['serial'])) {
                if ($this->serial === null) {
                    $this->serial = $ref->getName();
                } else {
                    throw new Exception("only one serial field per model is supported");
                }
                
            }
        }
    }
    
    private function scan_validations() {
        if (isset($this->annotations['validate'])) {
            foreach ($this->annotations['validate'] as $args) {
                $callback = array_shift($args);
                $options = is_array($args[count($args) - 1]) ? array_pop($args) : array();
                $this->validations[] = new ModelValidation($this, $callback, $args, $options);
            }
        }
        
        foreach ($this->properties() as $property) {
            $annotations = $property->get_annotations();
            if (isset($annotations['required'])) {
                $this->validations[] = new PropertyValidation($this, $property, '!::is_empty');
            }
            if (isset($annotations['validate'])) {
                foreach ($annotations['validate'] as $args) {
                    $callback = array_shift($args);
                    $options = is_array($args[count($args) - 1]) ? array_pop($args) : array();
                    $this->validations[] = new PropertyValidation($this, $property, $callback, $args, $options);
                }
            }
        }
    }
}
?>
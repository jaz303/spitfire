<?php
namespace spitfire;

class Property
{
    private $model;
    private $name;
    private $type;
    
    public function __construct(Model $model, $name, $type) {
        
        $this->model        = $model;
        $this->name         = $name;
        $this->type         = $type;
        
        if (!$model->has_attribute($name)) {
            $model->add_attribute(new Attribute($model, $name, $type));
        }
        
    }
    
    public function name() { return $this->name; }
    public function type() { return $this->type; }
    
    public function getter_name() { return "get_{$this->name}"; }
    public function getter_before_typecast_name() { return $this->getter_name() . '_before_typecast'; }
    public function setter_name() { return "set_{$this->name}"; }
    
    public function render_class_body() {
        
        $php = "";
        
        if (!$this->model->get_reflection()->hasProperty("{$this->name}_before_typecast")) {
            $php .= "protected \${$this->name}_before_typecast;\n\n";
        }
        
        $getter = "get_{$this->name}";
        if (!$this->method_exists($getter)) {
            $php .= "public function $getter() {\n";
            $php .= "    return \$this->{$this->name};\n";
            $php .= "}\n\n";
        }
        
        $getter = "get_{$this->name}_before_typecast";
        if (!$this->method_exists($getter)) {
            $php .= "public function $getter() {\n";
            $php .= "    return \$this->{$this->name}_before_typecast;\n";
            $php .= "}\n\n";
        }
        
        // TODO: need to do typecasting here!
        $setter = "set_{$this->name}";
        if (!$this->method_exists($setter)) {
            $php .= "public function $setter(\$value) {\n";
            $php .= "    \$this->{$this->name}_before_typecast = \$value;\n";
            $php .= "}\n\n";
        }
        
        return $php;
        
    }
    
    private function method_exists($method_name) {
        return $this->model->get_reflection()->hasMethod($method_name);
    }
}
?>
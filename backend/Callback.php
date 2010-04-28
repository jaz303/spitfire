<?php
namespace spitfire;

class Callback
{
    public static function invocation($description, $preferred_static_class = null, $arguments = array()) {
        $php = self::resolve($description, $preferred_static_class);
        $php .= '(';
        $chr = '';
        foreach ($arguments as $a) {
            $php .= $chr;
            if (is_object($a)) {
                $php .= $a->to_php();
            } else {
                $php .= var_export($a, true);
            }
            $chr .= ', ';
        }
        $php .= ')';
        return $php;
    }
    
    public static function resolve($descriptor, $preferred_static_class = null) {
        if ($descriptor[0] == '-' && $descriptor[1] == '>') {
            return '$this->' . substr($descriptor, 2);
        } elseif (strpos($descriptor, '::') == 0) {
            if ($preferred_static_class === null) {
                throw new Exception("preferred class is null");
            } else {
                return $preferred_static_class . '::' . $descriptor . '()';
            }
        } else {
            return $descriptor;
        }
    }
    
    
}

class Variable
{
    private $name;
    
    public function __construct($name = 'this') {
        $this->name = $name;
    }
    
    public function to_php() {
        return "\${$this->name}";
    }
}
?>
<?php
namespace spitfire;

class Utilities
{
    public static function calculate_type($reflection) {
        $annotations = Annotation::parse($reflection);
        if (isset($annotations['type'])) {
            return $annotations['type'];
        } else {
            $defaults = $reflection->getDeclaringClass()->getDefaultProperties();
            $default_value = $defaults[$reflection->getName()];
            if (is_null($default_value)) {
                return 'null';
            } if (is_string($default_value)) {
                return 'string';
            } else if (is_integer($default_value)) {
                return 'integer';
            } else if (is_float($default_value)) {
                return 'float';
            } else if (is_bool($default_value)) {
                return 'boolean';
            } else if (is_array($default_value)) {
                return 'array';
            }
        }
    }
}
?>
<?php
namespace spitfire;

class Universe
{
    private $models     = array();
    
    public function register_model(Model $model) {
        $this->models[$model->get_class_name()] = $model;
    }
    
    public function get_models() {
        return array_values($this->models);
    }
    
}
?>
<?php
namespace spitfire;

class Builder
{
    private $files = array();
    private $paths = array();
    private $extensions = array();
    
    private $class_files = array();
    
    private $hierarchy;
    private $universe;
    
    private $class_indent = '    ';
    
    public function add_file($file) { $this->files[] = $file; }
    public function add_path($path) { $this->paths[] = $path; }
    public function add_extension($ext) { $this->extensions[] = $ext; }
    
    public function build() {
        $this->universe = new Universe;
        $this->populate_file_list();
        $this->zap_files();
        $this->build_class_file_map();
        $this->require_files();
        $this->treeify();
        $this->descend($this->hierarchy['!root!']);
        $this->render();
    }
    
    private function populate_file_list() {
        // TODO
    }
    
    private function zap_files() {
        foreach ($this->files as $file) {
            $this->spitfire_replace($file, '');
        }
    }
    
    private function build_class_file_map() {
        foreach ($this->files as $file) {
            $tokens = token_get_all(file_get_contents($file));
            $count = count($tokens);
            for ($i = 2; $i < $count; $i++) {
                if ($tokens[$i - 2][0] == T_CLASS &&
                    $tokens[$i - 1][0] == T_WHITESPACE &&
                    $tokens[$i - 0][0] == T_STRING) {
                    $class_name = $tokens[$i][1];
                    echo "found class {$class_name} in $file\n";
                    $this->class_files[$class_name] = $file;    
                }
            }
        }
    }
    
    private function require_files() {
        foreach ($this->files as $file) {
            require $file;
        }
    }
    
    private function treeify() {
        $this->hierarchy = array();
        foreach (get_declared_classes() as $class_name) {
            $parent_class = get_parent_class($class_name);
            if (!$parent_class) $parent_class = '!root!';
            $this->hierarchy[$parent_class][] = $class_name;
        }
    }
    
    private function descend($classes) {
        foreach ($classes as $class) {
            $this->handle_class($class);
        }
        foreach ($classes as $class) {
            if (isset($this->hierarchy[$class])) {
                $this->descend($this->hierarchy[$class]);
            }
        }
    }
    
    private function handle_class($class) {
        $reflection = new \ReflectionClass($class);
        $annotations = Annotation::parse($reflection);
        
        if (isset($annotations['model'])) {
            echo "creating model {$reflection->getName()}\n";
            $model = new Model($this->universe, $reflection);
            $this->universe->register_model($model);
        }
    }
    
    private function render() {
        foreach ($this->universe->get_models() as $model) {
            echo "writing model {$model->get_class_name()}\n";
            
            $body = $model->render_class_body();
            $body = preg_replace('/^/m', $this->class_indent, $body);
            $body = "{$this->class_indent}\n" . $body;
            
            $this->spitfire_replace($this->class_files[$model->get_class_name()], $body);
        }
    }
    
    private function spitfire_replace($file, $code) {
        $lines = file($file);
        $fh = fopen($file, 'w');
        $in = false;
        foreach ($lines as $line) {
            if (!$in) {
                fwrite($fh, $line);
                if (preg_match('/^\s*\/\/\s*SPITFIRE-BEGIN\s+$/', $line)) {
                    $in = true;
                    fwrite($fh, $code);
                }
            } else {
                if (preg_match('/^\s*\/\/\s*SPITFIRE-END\s+$/', $line)) {
                    $in = false;
                    fwrite($fh, $line);
                }
            }
        }
    }
}
?>
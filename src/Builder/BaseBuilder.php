<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseParser
 *
 * @author Dinkic
 */
namespace Dinkara\RepoBuilder\Builder;

use Illuminate\Support\Str;

abstract class BaseBuilder {
    
    const START_SYMBOL = "{{";
    const END_SYMBOL = "}}";
    const EXTENSION = ".php";
    const PATH = "";
    protected $patterns;
    
    protected $template_path = "//vendor//dinkara//repobuilder//src//Templates//";    
    protected $base_save_path = "";
    protected $filename = "";    
    
    public function __construct($name){
        $this->name = $name;  
        $this->template_path = base_path() . $this->template_path;
    }
       
    abstract function save();

    protected function parseAndSave(){
        $save_path = $this->base_save_path . $this->filename;
        
        if (!copy($this->template_path, $save_path)) {           
        }       
        $template = $this->parse();
        
        file_put_contents($save_path, $template);
    }
    
    private function parse(){            
        $template = file_get_contents($this->template_path);       
        foreach ($this->patterns as $key => $pattern) {            
            $template = str_replace($key, $pattern, $template);  
        }
        return $template;
    }
    
    /**
     * Generate model name based on $name
     * @return type
     */
    protected function modelName() {
        return ucfirst(Str::singular(Str::camel($this->name)));
    }
    
    /**
     * Generate table name based $name
     * @param type $name
     * @return type
     */
    protected function tableName() {
        return Str::plural(Str::snake($this->name));
    }
    
    /**
     * Generate file name for migration file, based on $name
     * @return type
     */
    protected function migrationFile() {
        return "create_" . self::tableName($this->name) . "_table";
    }
    
    /**
     * Generate filename for model based on $name
     * @return type
     */
    protected function modelFile() {
        return self::modelName($this->name);
    }
    
}

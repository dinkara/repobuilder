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

class RepositoryBuilder extends BaseBuilder{
    
    const PATH = "Repositories//";
    const MODEL = self::START_SYMBOL . "model" . self::END_SYMBOL;
    const EXTENSION = ".php";
    protected $interface = "IRepo.php";
    protected $eloquent = "EloquentRepo.php";
    protected $prefix = "";
    protected $sufix = "";
    protected $patterns = [
        self::MODEL => '',
    ];
    
    public function __construct($name){
        parent::__construct($name);  
        $this->base_save_path = app_path(self::PATH);
        $this->checkExisting();
        $this->patterns[self::MODEL] = $this->modelName();
        $this->base_save_path .= $this->modelName() . "//";
        $this->filename = $this->prefix .$this->modelName() . $this->sufix . self::EXTENSION;
    }
       
    public function save(){
        $this->parseAndSave();
    }
    
    protected function checkExisting(){
        if(!is_dir($this->base_save_path)){
            if(mkdir($this->base_save_path)){
            }
        }
    }
       
}

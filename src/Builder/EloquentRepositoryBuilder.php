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

class EloquentRepositoryBuilder extends RepositoryBuilder{
    protected $prefix = "Eloquent";
    protected $sufix = "";

    public function __construct($name, $es = false ){
        parent::__construct($name, $es);
        $this->template_path .= $this->prefix . self::EXTENSION;
        $this->checkExisting();
        
    }
    
    protected function checkExisting(){
        if(!is_dir($this->base_save_path)){
            if(mkdir($this->base_save_path)){
                
            }
        }
    }
       
}

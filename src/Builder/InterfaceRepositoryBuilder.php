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

class InterfaceRepositoryBuilder extends RepositoryBuilder{
    const MODEL = self::START_SYMBOL . "model" . self::END_SYMBOL;
    protected $prefix = "I";
    protected $sufix = "Repo";

    public function __construct($name, $es = false){
        parent::__construct($name, $es);
        $this->template_path .= $this->sufix . self::EXTENSION;
        $this->checkExisting();

    }

    protected function checkExisting(){
        if(!is_dir($this->base_save_path)){
            if(mkdir($this->base_save_path)){

            }
        }
    }

}

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

use Illuminate\Support\Facades\Artisan;

class ModelBuilder extends BaseBuilder{
    
    const PATH = "Models/";
    
    public function __construct($name){
        parent::__construct($name);  
        $this->base_save_path = app_path(self::PATH);
    }
       
    public function save(){
        Artisan::call('make:model', [
            'name' => self::PATH . $this->modelFile()
        ]);
    }
       
}

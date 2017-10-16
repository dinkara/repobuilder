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

class MigrationBuilder extends BaseBuilder{
    
    public function save(){
        Artisan::call('make:migration', [
            'name' => $this->migrationFile(),
            '--create' => $this->tableName(),
        ]);
    }
       
}

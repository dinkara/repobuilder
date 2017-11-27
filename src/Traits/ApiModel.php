<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dinkara\RepoBuilder\Traits;
use Sofa\Eloquence\Eloquence;

trait ApiModel{
      
    use Eloquence;
    
    public function getDisplayable() {
        return isset($this->displayable) ? $this->displayable : [];
    }
    
}

<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dinkara\RepoBuilder\Traits;
use Sofa\Eloquence\Eloquence;
use Illuminate\Database\Eloquent\Relations\Relation;

trait ApiModel{
      
    use Eloquence;

    public function getDisplayable() {
        return isset($this->displayable) ? $this->displayable : [];
    }

    /**
     * Generate query from input data
     * 
     * @param  \Illuminate\Database\Eloquent\Builder  $relation
     * @param type $q exp. ?q=test
     * @param type $orderBy exp. &orderBy=name,caption,asc,id,desc
     * @return type
     */
    public function searchRelation(Relation  $relation, $q, $orderBy = null) {
        $query = $q ? $relation->search(["*" . $q . "*"], false) : $relation;

        $orderDirections = ['asc', 'desc'];
        if($orderBy){
            $orderByArray = explode(",", $orderBy);
            $t=0;
            for($i=0;$i<count($orderByArray);$i++) {
                if(in_array($orderByArray[$i], $orderDirections)){
                    for($j=$t;$j<$i;$j++){
                        $query = $query->orderBy($orderByArray[$j], $orderByArray[$i]);                        
                    }
                    $t=$i+1;
                }
            }                                                
        }  
        return $query;
    }
}

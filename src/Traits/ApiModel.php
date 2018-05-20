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
    public function searchRelation(Relation $relation, $q, $orderBy = null) {
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

    /**
     * @param Relation $relation - base query [hasOne, hasMany, belongsTo, belongsToMany]
     * @param null $q  exp. ?q=test
     * @param null $orderBy exp. &orderBy=name,caption,asc,id,desc
     * @param array $searchFields axp. ['name' => '?', 'operator' => '??', 'value' => '???'] => associative array explanation ? - name of amodel attribute, ?? - operator can be [=, !=, like, not like, etc], ??? - wanted value of attribute
     * @return type Relation
     */
    protected function baseSearchQuery(Relation $relation, $q = null, $orderBy = null, $searchFields = []){

        if(is_array($searchFields)) {
            foreach ($searchFields as $field) {
                $field['operator'] = key_exists('operator', $field) ? $field['operator'] : '=';
                $relation->where($field['name'], $field['operator'], $field['value']);
            }
        }
        if($q === null && $orderBy === null){
            return $relation;
        }
        return $this->searchRelation($relation, $q, $orderBy);
    }

}

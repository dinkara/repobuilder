<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dinkara\RepoBuilder\Traits;
use Dinkara\RepoBuilder\Utils\AvailableRestQueryParams;
use Sofa\Eloquence\Eloquence;
use Illuminate\Database\Eloquent\Relations\Relation;
use DB;

trait ApiModel{
      
    use Eloquence;

    public function getLimit() {
        return isset($this->limit) ? $this->limit : AvailableRestQueryParams::DEFAULT_LIMIT;
    }

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
    public function searchRelation(Relation $relation, $q) {

        if($q == null){
            return $relation;
        }

        $query = $q ? $relation->search(["*" . $q . "*"], false) : $relation;

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

    /**
     * Get all table attributes
     *
     * @return array
     */
    public function getAllColumnsNames()
    {
        switch (DB::connection()->getConfig('driver')) {
            case 'pgsql':
                $query = "SELECT column_name FROM information_schema.columns WHERE table_name = '".$this->getTable()."'";
                $column_name = 'column_name';
                $reverse = true;
                break;

            case 'mysql':
                $query = 'SHOW COLUMNS FROM '.$this->getTable();
                $column_name = 'Field';
                $reverse = false;
                break;

            case 'sqlsrv':
                $parts = explode('.', $this->getTable());
                $num = (count($parts) - 1);
                $table = $parts[$num];
                $query = "SELECT column_name FROM ".DB::connection()->getConfig('database').".INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'".$table."'";
                $column_name = 'column_name';
                $reverse = false;
                break;

            default:
                $error = 'Database driver not supported: '.DB::connection()->getConfig('driver');
                throw new \Exception($error);
                break;
        }

        $columns = array();

        foreach(DB::select($query) as $column)
        {
            array_push($columns, $column->$column_name);
        }

        if($reverse)
        {
            $columns = array_reverse($columns);
        }

        return $columns;
    }



}

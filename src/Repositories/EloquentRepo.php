<?php

namespace Dinkara\RepoBuilder\Repositories;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseRepo
 *
 * @author ndzak
 */
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

abstract class EloquentRepo implements IRepo {

    protected $model;

    protected $attributes;

    const FIND_BY = "findBy";
    //Available operators
    const OPERATOR_BETWEEN = "between";
    const OPERATOR_NOT_BETWEEN = "notbetween";
    const OPERATOR_IN = "in";
    const OPERATOR_NOT_IN = "notin";
    const OPERATOR_NULL = "null";
    const OPERATOR_NOT_NULL = "notnull";
    const OPERATOR_COLUMN = "column";
    const OPERATOR_GREAT = ">";
    const OPERATOR_LESS = "<";

    protected $orderDirections = ["asc","desc"];

    abstract public function model();

    public function getModel() {
        if (!$this->model)
            $this->initialize();

        return $this->model;
    }

    public function firstOrNew($where) {
        $this->initialize();

        $this->model = $this->model->firstOrNew($where);

        return $this->finalize($this->model);
    }

    public function firstOrCreate($where) {
        $this->initialize();

        $this->model = $this->model->firstOrCreate($where);

        return $this->finalize($this->model);
    }

    public function fill($fields) {
        $this->initialize();

        $this->model->fill($fields);

        return $this->finalize($this->model);
    }

    public function find($id) {
        $this->initialize();

        $this->model = $this->model->find($id);

        return $this->finalize($this->model);
    }

    public function all(){
        $this->initialize();

        return $this->model->all();
    }

    public function paginateAll($perPage = 10) {
        $this->initialize();
        return $this->model->paginate($perPage);
    }

    public function create($fields) {

        $this->initialize();

        $this->model->fill($fields);

        return $this->save();
    }

    public function findAndUpdate($id, $fields) {
        $this->initialize();

        $item = $this->find($id);

        if (!$item) {
            return false;
        }

        return $this->update($fields);
    }

    public function update($fields) {
        if (!$this->model) {
            return false;
        }

        $result = $this->model->update($fields);

        return $this->finalize($result);
    }

    public function save() {
        if (!$this->model) {
            return false;
        }

        $result = $this->model->save();

        return $this->finalize($result);
    }

    public function delete() {
        if (!$this->model) {
            return false;
        }

        return $this->model->delete();
    }

    public function searchAndPaginate($q, $orderBy = null, $perPage = 10) {
        if($query = $this->makeSearchQuery($q, $orderBy)){
            return $query->paginate($perPage);
        }
        return null;
    }

    public function search($q, $orderBy = null) {
        if($query = $this->makeSearchQuery($q, $orderBy)){
            return $query->get();
        }
        return null;
    }

    /**
     * @param $data ['key' => 'name', 'value' => 'Nick', 'operator' => '='] - operator is optional ( = is by default)
     * @return first Object
     */
    public function findBy($data = []){
        return $this->searchQuery($data)->first();
    }

    /**
     * @param $data ['key' => 'name', 'value' => 'Nick', 'operator' => '='] - operator is optional ( = is by default)
     * @param array $sort ['key1' => true,'key2' => false ] - true means ASC, false DESC
     * @return array of Models
     */
    public function searchBy($data = [], $sort = []){
        return $this->searchQuery($data, $sort)->get();
    }

    /**
     * @param $data ['key' => 'name', 'value' => 'Nick', 'operator' => '='] - operator is optional ( = is by default)
     * @param array $sort ['key1' => true,'key2' => false ] - true means ASC, false DESC
     * @param int $perPage
     * @return array of Models
     */
    public function searchByPaginated($data = [], $sort = [], $perPage = 10){
        return $this->searchQuery($data, $sort)->paginate($perPage);
    }


    public function __call($name, $arguments) {
        $this->initialize();
        $column = Str::snake(str_replace(self::FIND_BY, '', $name));
        if(in_array($column, $this->attributes)){
            $this->model = $this->model->where($column, '=', $arguments[0])->first();
            return $this->finalize($this->model);
        }
        return [];
    }

    //=========================
    //PROTECTED SECTION
    //=========================

    protected function initialize() {
        $this->model = $this->model();
        $this->attributes = Schema::connection($this->model->getConnectionName())->getColumnListing($this->model->getTable());
    }

    protected function finalize($result) {
        if ($result) {
            return $this;
        }

        //log error or throw exception
    }

    /**
     * Prepare eloquent query based on given array
     *
     * @param $query
     * @param array $data
     * @param array $sort
     * @return mixed
     */
    protected function baseSearchQuery($query, $data = [], $sort = []){
        if(is_array($data)){
            foreach($data as $item){
                if(in_array($item['key'], $this->attributes)) {
                    $operator = key_exists('operator', $item) ? $item['operator'] : '=';
                    if ($operator == self::OPERATOR_IN) {
                        $query = $query->whereIn($item['key'], $item['value']);
                    } else if ($operator == self::OPERATOR_NOT_IN) {
                        $query = $query->whereNotIn($item['key'], $item['value']);
                    } else if ($operator == self::OPERATOR_NULL) {
                        $query = $query->whereNull($item['key']);
                    } else if ($operator == self::OPERATOR_NOT_NULL) {
                        $query = $query->whereNotNull($item['key']);
                    } else if ($operator == self::OPERATOR_BETWEEN) {
                        $query = $query->whereBetween($item['key'], [$item['value1'], $item['value2']]);
                    } else if ($operator == self::OPERATOR_NOT_BETWEEN) {
                        $query = $query->whereNotBetween($item['key'], [$item['value1'], $item['value2']]);
                    } else if ($operator == self::OPERATOR_COLUMN) {
                        $query = $query->whereColumn($item['key1'], $item['operator'], $item['key2']);
                    } else {
                        $query = $query->where($item['key'], $operator, $item['value']);
                    }
                }
            }
        }
        if(is_array($sort)) {
            foreach ($sort as $key => $value) {
                if(in_array($key, $this->attributes)) {
                    $query = $query->orderBy($key, $value ? 'asc' : 'desc');
                }
            }
        }

        return $query;
    }


    //=========================
    //PRIVATE SECTION
    //=========================

    /**
     * Generate query from input data
     *
     * @param type $q
     * @param type $orderBy
     * @return type
     */
    private function makeSearchQuery($q, $orderBy = null) {
        $query = $q ? $this->model()->search(["*" . $q . "*"], false) : $this->model();

        if($orderBy){
            $orderByArray = explode(",", $orderBy);
            $t=0;
            for($i=0;$i<count($orderByArray);$i++) {
                if(in_array($orderByArray[$i], $this->orderDirections)){
                    for($j=$t;$j<$i;$j++){
                        $query = $query->orderBy($orderByArray[$j], $orderByArray[$i]);
                    }
                    $t=$i+1;
                }
            }
        }
        return $query;
    }

    private function searchQuery($data = []){
        if (!$this->model)
            $this->initialize();
        return $this->baseSearchQuery($this->model, $data);
    }
}

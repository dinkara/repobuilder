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
use Dinkara\RepoBuilder\Utils\QueryBuilder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use BadMethodCallException;
use Dinkara\Utils\Filter;
use Illuminate\Http\Request;

abstract class EloquentRepo implements IRepo {

    protected $model;

    protected $attributes;

    const FIND_BY = "findBy";
    const SYNC = "sync";
    const IS_ATTACHED_TO = "isAttachedTo";
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

    public function setModel($model) {
        $this->model = $model;

        return $this->finalize($this->model);
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

    public function restSearch(Request $request, $query = null, $returnQueryBuilder = false) {
        $queryBuilder = new QueryBuilder($request, $this, $query);
        return $returnQueryBuilder ? $queryBuilder->getQuery() : $queryBuilder->getData();
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

    public function paginate($perPage = 10) {
        if(!$this->model) {
            $this->initialize();
        }
        return $this->model->paginate($perPage);
    }

    public function get() {
        if(!$this->model) {
            $this->initialize();
        }
        return $this->model->get();
    }

    public function count() {
        if(!$this->model) {
            $this->initialize();
        }
        return $this->model->count();
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

    /** Mass insert function for model
     * @param array $data
     * @return $this|bool
     */
    public function bulk(array $data = []){
        $this->initialize();

        return $this->finalize($this->model->insert($data));
    }

    /** Mass delete function for model
     * @param array $data ids
     * @return $this|bool
     */
    public function bulkDelete(array $data = []){
        $this->initialize();

        return $this->finalize($this->model->destroy($data));
    }

    /**
     * Sync $relations with or without detaching
     *
     * @param $relation is name of model relation
     * @param array $data [id => [attr1 => $value1, $attr2 => $value2], id2 => [attr1 => $value1, $attr2 => $value2]]
     * @param $detach is flag to determine should sync() or syncWithoutDetaching() be used
     * @return $this|bool
     */
    public function sync($relation , array $data = [], $detach = true){
        if (!$this->model) {
            return false;
        }

        $result = null;
        if($detach){
            $result = $this->model->{$relation}()->sync($data);
        }
        else{
            $result = $this->model->{$relation}()->syncWithoutDetaching($data);
        }

        return $this->finalize($result);
    }


    /** Checks if model is already attached to given relation
     * @param $relation name of Model relation
     * @param $id of related model
     * @return bool
     */
    public function isAttachedTo($relation, $id){
        //ovde bi trebao exception
        if (!$this->model) {
            return false;
        }

        return $this->model->{$relation}()->find($id) != null;
    }

    public function __get( $key )
    {
        if ($this->model && in_array($key, $this->attributes)) {
            return $this->model->{$key};
        }

        return null;
    }

    public function __set( $key, $value )
    {
        if ($this->model && in_array($key, $this->attributes)) {
            $this->model->{$key} = $value;
        }
    }

    public function __call($name, $arguments) {

        //Model relations
        if(method_exists($this->model, $name)){
            if(count($arguments) > 0){
                return $this->model->{$name} (  ...$arguments );
            }else{
                return $this->model->{$name};
            }
        }
        //if isAttachedTo magic function is called
        if(Str::startsWith($name, self::IS_ATTACHED_TO)) {
            $relation = Str::plural(lcfirst(str_replace(self::IS_ATTACHED_TO, '', $name)));
            return $this->isAttachedTo($relation, $arguments[0]);
        }

        //if findBY magic function is called
        if(Str::startsWith($name, self::FIND_BY)) {
            $this->initialize();
            $column = Str::snake(str_replace(self::FIND_BY, '', $name));
            if (in_array($column, $this->attributes)) {
                $this->model = $this->model->where($column, '=', $arguments[0])->first();
                return $this->finalize($this->model);
            }
        }

        //if sync magic function is called
        if(Str::startsWith($name, self::SYNC)) {
            $relation = lcfirst(str_replace(self::SYNC, '', $name));
            $detach = true;
            if(count($arguments) > 1){
                $detach = $arguments[1];
            }
            return $this->sync($relation, $arguments[0], $detach);
        }

        if(!$this->model){
            $this->initialize();
        }

        if (in_array(Str::snake($name), $this->attributes)) {

            $operator = '=';
            if(count($arguments) > 1){
                $operator = $arguments[1];
            }

            $this->model = $this->apply($this->model, $operator, Str::snake($name), $arguments[0]);

            return $this->finalize($this->model);
        }

        if(method_exists($this, $name)){
            return $this->{$name};
        }

        if(method_exists($this->model, $name)){
            return $this->model->{$name};
        }

        throw new BadMethodCallException("Method '$name' not implemented!");
    }

    /**
     * Prepare eloquent query based on given array
     *
     * @param $query
     * @param array $data
     * @param array $sort
     * @return mixed
     */
    public function baseSearchQuery($query, $data = [], $sort = []){
        if(is_array($data)){
            foreach($data as $item){
                //if(in_array($item['key'], $this->attributes)) {
                    $operator = key_exists('operator', $item) ? $item['operator'] : '=';

                    $value = $item['value'];
                    $key = $item['key'];
                    if ($operator == self::OPERATOR_BETWEEN || $operator == self::OPERATOR_NOT_BETWEEN) {
                        $value =  [$item['value1'], $item['value2']];
                    } else if ($operator == self::OPERATOR_COLUMN) {
                        $key = $item['key1'];
                        $value =  $item['key2'];
                    }
                    $query = $this->apply($query, $operator, $key, $value);
                //}
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

    private function apply($query, $operator, $key, $value = null){
        if ($operator == self::OPERATOR_IN) {
            return $query->whereIn($key, $value);
        } else if ($operator == self::OPERATOR_NOT_IN) {
            return $query->whereNotIn($key, $value);
        } else if ($operator == self::OPERATOR_NULL) {
            return $query->whereNull($key);
        } else if ($operator == self::OPERATOR_NOT_NULL) {
            return $query->whereNotNull($key);
        } else if ($operator == self::OPERATOR_BETWEEN) {
            return $query->whereBetween($key, $value);//USE SPREAD OPERATOR FOR VALUE
        } else if ($operator == self::OPERATOR_NOT_BETWEEN) {
            return $query->whereNotBetween($key, $value);//USE SPREAD OPERATOR FOR VALUE
        } else if ($operator == self::OPERATOR_COLUMN) {
            return $query->whereColumn($key, $operator, $value);
        } else {
            return $query->where($key, $operator, $value);
        }
    }
    //OLD
    /*if ($operator == self::OPERATOR_IN) {
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
    }*/

    private function searchQuery($data = []){
        if (!$this->model)
            $this->initialize();
        return $this->baseSearchQuery($this->model, $data);
    }
}

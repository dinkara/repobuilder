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

        $this->model = $item;

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
        $this->attributes = Schema::getColumnListing($this->model->getTable());
    }

    protected function finalize($result) {
        if ($result) {
            return $this;
        }
                
        //log error or throw exception
    }

}

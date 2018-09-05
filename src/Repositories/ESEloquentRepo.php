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

abstract class ESEloquentRepo extends EloquentRepo implements IRepo, IESRepo {

    const FIND_BY_RAW = "findByRaw";

    /**
     * Override base all to work with Elastic Search
     *
     * @return mixed
     */
    public function all(){
        $this->initialize();

        return $this->model->search('')->all();
    }

    /**
     * Override base pagginateALl to work with Elastic Search
     *
     * @param int $perPage
     * @return mixed
     */
    public function paginateAll($perPage = 10) {
        $this->initialize();
        return $this->model->search('')->paginate($perPage);
    }

    /**
     * @param $data ['key' => 'name', 'value' => 'Nick', 'operator' => '='] - operator is optional ( = is by default)
     * @return first Object
     */
    public function findByRaw($data = []){
        return $this->searchQueryRaw($data)->first();
    }

    /**
     * @param $data ['key' => 'name', 'value' => 'Nick', 'operator' => '='] - operator is optional ( = is by default)
     * @return array of Models
     */
    public function searchByRaw($data = []){
        return $this->searchQueryRaw($data)->raw();
    }

    /**
     * @param $data ['key' => 'name', 'value' => 'Nick', 'operator' => '='] - operator is optional ( = is by default)
     * @return array of Models
     */
    public function searchByPaginated($data = [], $perPage = 10){
        return $this->searchQueryRaw($data)->paginate($perPage);
    }

    public function __call($name, $arguments) {
        $this->initialize();
        $column = Str::snake(str_replace(self::FIND_BY_RAW, '', $name));
        if(in_array($column, $this->attributes)){
            $this->model = $this->model->search('')->where($column, '=', $arguments[0])->first();
            return $this->finalize($this->model);
        }
        return [];
    }

    //=========================
    //PRIVATE SECTION
    //=========================


    private function searchQueryRaw($data = []){
        if (!$this->model)
            $this->initialize();
        return $this->baseSearchQuery($this->model->search(''), $data);
    }
}

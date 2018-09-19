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
     * Override base pagginateALl to work with Elastic Search
     *
     * @param int $perPage
     * @return mixed
     */
    public function paginateAllRaw($perPage = 10) {
        $this->initialize();
        return $this->model->search('*')->paginate($perPage);
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
     * @param array $sort ['key1' => true,'key2' => false ] - true means ASC, false DESC
     * @return array of Models
     */
    public function searchByRaw($data = [], $sort = []){
        return $this->searchQueryRaw($data, $sort)->raw();
    }

    /**
     * @param $data ['key' => 'name', 'value' => 'Nick', 'operator' => '='] - operator is optional ( = is by default)
     * @param array $sort ['key1' => true,'key2' => false ] - true means ASC, false DESC
     * @param int $perPage
     * @return array of Models
     */
    public function searchByPaginatedRaw($data = [], $sort = [], $perPage = 10){
        return $this->searchQueryRaw($data, $sort)->paginate($perPage);
    }

    public function __call($name, $arguments) {
        $this->initialize();
        if(strpos($name, self::FIND_BY_RAW) !== true){
            return parent::__call($name, $arguments);
        }
        $column = Str::snake(str_replace(self::FIND_BY_RAW, '', $name));
        if(in_array($column, $this->attributes)){
            $this->model = $this->model->search('*')->where($column, '=', $arguments[0])->first();
            return $this->finalize($this->model);
        }
        return [];
    }

    //=========================
    //PRIVATE SECTION
    //=========================


    private function searchQueryRaw($data = [], $sort = []){
        if (!$this->model)
            $this->initialize();
        return $this->baseSearchQuery($this->model->search('*'), $data, $sort);
    }
}

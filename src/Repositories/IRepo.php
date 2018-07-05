<?php

namespace Dinkara\RepoBuilder\Repositories;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author ndzak
 */
interface IRepo {

    function model();

    function firstOrNew($where);

    function firstOrCreate($where);

    function fill($fields);

    function find($id);

    function all();

    function paginateAll($perPage = 20);

    function create($fields);

    function findAndUpdate($id, $fields);

    function update($fields);

    function save();

    function delete();

    function searchAndPaginate($q, $orderBy = null, $perPage = 10);

    function search($q, $orderBy = null);

    /**
     * @param array ['key' => 'name', 'value' => 'Nick', 'operator' => '='] - operator is optional ( = is by default)
     * @return first Object
     */
    public function findBy($data = []);

    /**
     * @param array ['key' => 'name', 'value' => 'Nick', 'operator' => '='] - operator is optional ( = is by default)
     * @return array of Models
     */
    public function searchBy($data = []);

    function __call($name, $arguments);
}

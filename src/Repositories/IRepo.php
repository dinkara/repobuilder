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

    function setModel($model);

    function firstOrNew($where);

    function firstOrCreate($where);

    function fill($fields);

    function find($id);

    function all();

    function paginateAll($perPage = 10);

    function create($fields);

    function findAndUpdate($id, $fields);

    function update($fields);

    function save();

    function delete();

    function searchAndPaginate($q, $orderBy = null, $perPage = 10);

    function search($q, $orderBy = null);

    function searchByPaginated($data = [], $sort = [], $perPage = 10);

    function findBy($data = []);

    function searchBy($data = []);

    function paginate($perPage = 10);

    function get();

    function count();

    function bulkDelete(array $data = []);

    function bulk(array $data = []);

    function sync($relation , array $data = [], $detach = true);

    function isAttachedTo($relation, $id);

    function __call($name, $arguments);

    function baseSearchQuery($query, $data = [], $sort = []);
}

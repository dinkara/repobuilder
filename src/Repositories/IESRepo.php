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
interface IESRepo extends IRepo{

    /**
     * Search direct to Elastic Search
     *
     * @param Array ['key' => 'name', 'value' => 'Nick', 'operator' => '='] - operator is optional ( = is by default)
     * @return first Object
     */
    public function findByRaw($data = []);

    /**
     * @param Array ['key' => 'name', 'value' => 'Nick', 'operator' => '='] - operator is optional ( = is by default)
     * @param array $sort ['key1' => true,'key2' => false ] - true means ASC, false DESC
     * @return array of Models
     */
    public function searchByRaw($data = [], $sort = []);

    /**
     * Search direct to Elastic Search. Return paginated items
     *
     * @param $data ['key' => 'name', 'value' => 'Nick', 'operator' => '='] - operator is optional ( = is by default)
     * @param array $sort ['key1' => true,'key2' => false ] - true means ASC, false DESC
     * @param int $perPage
     * @return array of Models
     */
    public function searchByPaginatedRaw($data = [], $sort = [], $perPage = 10);

}

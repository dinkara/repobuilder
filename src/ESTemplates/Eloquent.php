<?php

namespace App\Repositories\{{model}};

use Dinkara\RepoBuilder\Repositories\ESEloquentRepo;
use App\Models\{{model}};


class Eloquent{{model}} extends ESEloquentRepo implements I{{model}}Repo {


    public function __construct() {

    }

    /**
     * Configure the Model
     */
    public function model() {
        return new {{model}};
    }

}

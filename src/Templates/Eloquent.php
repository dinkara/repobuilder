<?php

namespace App\Repositories\{{model}};

use Dinkara\RepoBuilder\Repositories\EloquentRepo;
use App\Models\{{model}};


class Eloquent{{model}} extends EloquentRepo implements I{{model}}Repo {


    public function __construct() {

    }

    /**
     * Configure the Model
     */
    public function model() {
        return new {{model}};
    }

}

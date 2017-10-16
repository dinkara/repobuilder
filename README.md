# RepositoryBuilder

Repobuilder is addon for PHP Laravel framework that implements Repository pattern and adds an extra level for database communication in our project architecture. Instead of accessing models and writing queries in your Controller classes or Models, you should place them here. This way you will create single access point with controlled set of functions to your database. 

Main advantages are:

	* Code reusability
	* Code transparency
	* Global usage	
	* Scalable
	
## Requirements
* Laravel ~5.5 or higher

## Composer Install

``` bash
composer require dinkara/repobuilder
```

## Publish service

Add the service provider in `config/app.php`:

```php
Dinkara\RepoBuilder\RepositoryBuilderServiceProvider::class,
```

For publishing new services you need to execute the following line

``` bash
php artisan vendor:publish --provider="Dinkara\RepoBuilder\RepositoryBuilderServiceProvider"
```

Now you are ready to use new features. To check if everything is fine, execute following command

``` bash
php artisan list
```

If you can see make:repo option everything is ready.

## How to use command

Examples :

``` bash
php artisan make:repo User
```

In order to use your repositories as standard Laravel services you need to add following lines to your app/Providers/AppServiceProvider.php. This will point interface to proper class so Laravel knows which class to load when that interface is initialized.

``` php
    public function register()
    {
    	/*
	   $repos represents array of all  you created with this library	
	*/
        $repos = array(
            'User',  
        );
        
        foreach ($repos as $idx => $repo) {            
            $this->app->bind("App\Repositories\\{$repo}\I{$repo}Repo", "App\Repositories\\{$repo}\\Eloquent{$repo}");
        }
     }
```

This command will create new folder User under App\Repositories and make two  new files inside of them. Interface IUserRepo and class EloquentUser.

``` bash
php artisan make:repo User --migation
```

This command will create same like first command but it will also create new migration with given name. 

``` bash
php artisan make:repo User --model
```

This command will create same like first command but it will also create new model with given name in App\Models. 

``` bash
php artisan make:repo User --all
```

This command will create migration, model and necessary repository classes. 

## How to use repositories

Repositories created with this library can be used in Controllers, Services, Seeders or any other class in your Laravel application.

Basic example:

``` php
<?php

namespace App\Http\Controllers;

use App\Repositories\User\IUserRepo;

class UserController extends Controller
{
    protected $userRepo;
    
    public function __construct(IUserRepo $userRepo) {
        $this->userRepo = $userRepo;
    }
	
    public function show($id)
    {  
        if($item = $this->repo->find($id)){
		return response()->json($item->getModel());
	}
		
	return response('Not Found', 404);	
     }
     
    public function update(Request $request, $id)
    {  
        $data = $request->all();
        if($this->userRepo->find($id)->update($data)){
		return response("Ok", 200);
	}
	
	return response("Failed", 500);
     }
	
	/*
		...
		Your code
		...
	*/
	
}
``` 
Base interface with all available functions:

``` php
<?php

namespace Dinkara\RepoBuilder\Repositories;

interface IRepo {

    function model();
	
    function getModel();

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
    
    function __call($name, $arguments);
}
``` 
Function "\_\_call" is a PHP magic function. It can be used to query database by attribute name.

Example:
``` php
$this->userRepo->findByEmail($email);
```
This can only be used for "findBy" function and it is recommended to use it only in combination with attributes that are unique, because it returns only first record.

## Adding custom function to your repositories

If you want to add custom or override existing functions in your repositories, this can be done easily by changing interface and class for specific repository. 

Let's have a look at our User example:

You should first add new functions to App\Repositories\User\IUserRepo.php interface like shown below.
``` php
<?php

namespace App\Repositories\User;

use App\Repositories\IRepo;
/**
 * Interface UserRepository
 * @package App\Repositories\User
 */
interface IUserRepo extends IRepo { 
    /**
     * Function that creates new user
     * @param type $fields
     */
    function register($fields);
    
	/*
		...
		Your custom functions
		...
	*/
}
```
Afterwards you should define that functions in App\Repositories\User\EloquentUser.php class.

``` php
<?php

namespace App\Repositories\User;

use App\Repositories\EloquentRepo;
use App\Models\User;

class EloquentUser extends EloquentRepo implements IUserRepo {


    /**
     * Configure the Model
     * */
    public function model() {
        return new User;
    }
    
    public function register($fields) {

        $fields["confirmation_code"] = str_random(30);

        $result = $this->create($fields)->attachRole("customer");

        return $this->finalize($result);
    }


    private function attachRole($role) {
        if (!$this->model) {
            return false;
        }

        $result = $this->model->roles()->attach($role);
        
        return $this->finalize($this->model);
    }   

}
```
Repository pattern in this library is meant to cache state of your model object. This allows us to call multiple functions on the same model object (like we did with create and attachRole in the code above). To achieve this you have to return finalize function and pass it your model. 

Do note that not all functions are meant to save state (for example when you have complex queries that return collection of data), in that case you should clear state of your model and just return eloquent result.

### __All suggestions and advices are welcome! So please send us your feedback and we will try to improve this library__

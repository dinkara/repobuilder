# RepositoryBuilder

## Install
Add the following line in your composer.json file to require RepositoryBuilder
``` php
"Dinkara/RepoBuilder" : "0.1.*"
```
And the following snippet to specify path to private repository

``` php
	"repositories": [
		{
			"type" : "git",
			"url" : "https://github.com/dinkara/repobuilder.git"
		}
	],
```

And then run

``` bash
composer update
```

Add the service provider in `config/app.php`:

```php
Dinkara\RepoBuilder\RepositoryBuilderServiceProvider::class,
```

For publishing new commands you need to execute the following line

``` bash
php artisan vendor:publish --provider="Dinkara\RepoBuilder\RepositoryBuilderServiceProvider"
```



Now you are ready to use new features. For check is everything is fine execute the following line

``` bash
php artisan list
```

And if you can see make:repo option everything is done.

## Usage

Examples :

``` bash
php artisan make:repo User
```

This command will create new folder User under App\Repositories and make two  new files inside of them. Interface IUserRepo and class EloquentUser.

``` bash
php artisan make:repo User --migation
```

This command will create same like first command but it will also create new migration with given name. 

``` bash
php artisan make:repo User --model
```

This command will create same like first command but it will also create new migration with given name in App\Models. 

``` bash
php artisan make:repo User --all
```

This command will create all of them. 
<?php

namespace Dinkara\RepoBuilder\Console;

use Illuminate\Console\Command;
use Dinkara\RepoBuilder\Builder\ModelBuilder;
use Dinkara\RepoBuilder\Builder\MigrationBuilder;
use Dinkara\RepoBuilder\Builder\EloquentRepositoryBuilder;
use Dinkara\RepoBuilder\Builder\InterfaceRepositoryBuilder;

class RepositoryBuilder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repo {name} {--model} {--migration} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new repository classes EloquentName and INameRepo. ';
    
    protected $bar;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Execute the console command. Laravel <5.5
     *
     * @return mixed
     */
    public function fire(){
        $this->handle();
    }
    
    /**
     * Execute the console command. Laravel >=5.5
     *
     * @return mixed
     */
    public function handle()
    {
        $progress = $this->option('all') ? 4 : ($this->option('model') ? 3 : ($this->option('migration') ? 3 : 2) );
        $this->bar = $this->output->createProgressBar($progress);
        
        $this->bar->start();
        if ($this->option('all')) {
            $this->input->setOption('model', true);
            $this->input->setOption('migration', true);
        }
        if ($this->option('model')) {
            $this->createModel();
        }
        if ($this->option('migration')) {
            $this->createMigration();
        }      
        $this->createRepository();

    }
    
    /**
     * Create a model file for the repository.
     *
     * @return void
     */
    protected function createModel()
    {
        (new ModelBuilder($this->argument('name')))->save();  
        $this->bar->advance();
        $this->info(' Creating Model...');
    }
    
    /**
     * Create a migration file for the model.
     *
     * @return void
     */
    protected function createMigration()
    {
        (new MigrationBuilder($this->argument('name')))->save();
        
        $this->bar->advance();
        $this->info( ' Creating Migration...');
    }
    
    /**
     * Create a repository.
     *
     * @return void
     */
    protected function createRepository()
    {
        (new EloquentRepositoryBuilder($this->argument('name')))->save();    
        $this->bar->advance();
        $this->info(' Creating RepositoryEloquent...');
        (new InterfaceRepositoryBuilder($this->argument('name')))->save();
        $this->bar->advance();
        $this->info(' Creating RepositoryInterface...');
        $this->bar->finish();
        $this->info(' Finished');
    }
    
    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['all', 'all', InputOption::VALUE_NONE, 'Generate a migration and model for the repository'],
            
            ['model', 'model', InputOption::VALUE_NONE, 'Create a new migration file for the repository.'],

            ['migration', 'migration', InputOption::VALUE_NONE, 'Create a new migration file for the model.'],

        ];
    }
    

}

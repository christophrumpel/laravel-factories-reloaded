<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Commands;

use Christophrumpel\LaravelCommandFilePicker\Traits\PicksClasses;
use Illuminate\Console\GeneratorCommand;

class MakeFactoryReloadedCommand extends GeneratorCommand
{
    use PicksClasses;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:factory-reloaded';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new factory reloaded class.';

    /** @var string */
    protected $type = 'Factory';

    /** @var string */
    private $fullClassName;

    /** @var string */
    private $className;

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        //we will generate the path to the location where this class' file should get written
        $this->makeDirectory(config('factories-reloaded.factories_path'));

        $this->fullClassName = $this->askToPickModels(config('factories-reloaded.models_path'));
        $this->className = class_basename($this->fullClassName);

        $this->info("Thank you! $this->className it is.");
        $classPath = config('factories-reloaded.factories_path').'/'.$this->className.'Factory.php';

        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if (( ! $this->hasOption('force') || ! $this->option('force')) && $this->files->exists($classPath)) {
            $this->error($this->type.' already exists!');

            return false;
        }

        //we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->files->put($classPath, $this->sortImports($this->buildClass($this->fullClassName)));

        $this->info(config('factories-reloaded.factories_namespace') . '\\' . $this->className.$this->type . ' created successfully.');
    }

    /**
     * Build the directory .
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/make-factory.stub';
    }

    protected function getArguments()
    {
        return [];
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);

        return str_replace(['DummyFullModelClass', 'DummyModelClass', 'DummyFactory'],
            [$this->fullClassName, $this->className, $this->className.'Factory'], $stub);
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace([
            'DummyNamespace',
        ], [
            config('factories-reloaded.factories_namespace'),
        ], $stub);

        return $this;
    }
}

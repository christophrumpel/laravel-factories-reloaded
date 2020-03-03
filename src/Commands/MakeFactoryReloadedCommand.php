<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Commands;

use Christophrumpel\LaravelCommandFilePicker\ClassFinder;
use Christophrumpel\LaravelCommandFilePicker\Traits\PicksClasses;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;

class MakeFactoryReloadedCommand extends GeneratorCommand
{

    use PicksClasses;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:factory-reloaded
                            {model?}
                            {--factories_path=}
                            {--models_path=}
                            {--factories_namespace=}
                            {--force}';

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

    /** @var string */
    private $modelsPath;

    /** @var string */
    private $modelFile = '';

    /** @var string */
    private $factoriesPath;

    /** @var string */
    private $factoriesNamespace;

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $this->modelsPath = $this->option('models_path') ?? config('factories-reloaded.models_path');
        $this->factoriesPath = $this->option('factories_path') ?? config('factories-reloaded.factories_path');
        $this->factoriesNamespace = $this->option('factories_namespace') ?? config('factories-reloaded.factories_namespace');
        if ($this->argument('model')) {
            $class_finder = new ClassFinder(new Filesystem());
            $this->fullClassName = $class_finder->getFullyQualifiedClassNameFromFile($this->modelsPath.'/'.$this->argument('model') . '.php');
        }
        else {
            $this->fullClassName = $this->askToPickModels($this->modelsPath);
        }

        $this->className = class_basename($this->fullClassName);

        $this->info("Thank you! $this->className it is.");
        $classPath = $this->factoriesPath . '/' . $this->className . 'Factory.php';

        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((!$this->hasOption('force') || !$this->option('force')) && $this->files->exists($classPath)) {
            $this->error($this->type . ' already exists!');
            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($classPath);

        $this->files->put($classPath, $this->sortImports($this->buildClass($this->fullClassName)));
        $this->info($this->factoriesNamespace . '\\' . $this->className . $this->type . ' created successfully.');
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/make-factory.stub';
    }

    protected function getArguments()
    {
        return [];
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);

        return str_replace(['DummyFullModelClass', 'DummyModelClass', 'DummyFactory'],
            [$this->fullClassName, $this->className, $this->className . 'Factory'], $stub);
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace([
            'DummyNamespace',
        ], [
            $this->factoriesNamespace,
        ], $stub);

        return $this;
    }
}

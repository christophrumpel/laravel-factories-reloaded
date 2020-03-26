<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Commands;

use Christophrumpel\LaravelCommandFilePicker\ClassFinder;
use Christophrumpel\LaravelCommandFilePicker\Traits\PicksClasses;
use Christophrumpel\LaravelFactoriesReloaded\FactoryCollection;
use Christophrumpel\LaravelFactoriesReloaded\FactoryFile;
use Christophrumpel\LaravelFactoriesReloaded\LaravelFactoryExtractor;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;

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

    private string $fullClassName;

    private string $className;

    private string $modelsPath;

    private string $modelFile;

    private string $factoriesPath;

    private string $factoriesNamespace;

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        Config::set('factories-reloaded.models_path',
            $this->option('models_path') ?? config('factories-reloaded.models_path'));
        Config::set('factories-reloaded.factories_path',
            $this->option('factories_path') ?? config('factories-reloaded.factories_path'));
        Config::set('factories-reloaded.factories_namespace',
            $this->option('factories_namespace') ?? config('factories-reloaded.factories_namespace'));

        if ($this->argument('model')) {
            $classFinder = new ClassFinder(new Filesystem());
            $modelsToCreate = collect([['name' => $classFinder->getFullyQualifiedClassNameFromFile(config('factories-reloaded.models_path').'/'.$this->argument('model').'.php')]]);
        } else {
            //$this->fullClassName = $this->askToPickModels(config('factories-reloaded.models_path'));
            $modelsToCreate = $this->askToPickModels(config('factories-reloaded.models_path'));
        }

        $factoryCollection = FactoryCollection::fromModels($modelsToCreate->transform(function ($modelToCreate) {
            return $modelToCreate['name'];
        })
            ->toArray());

        if ($this->option('force')) {
            $factoryCollection->overwrite();
        }

        $laravelStatesGiven = (bool) $factoryCollection->all()
            ->filter(function (FactoryFile $factoryFile) {
                return $factoryFile->hasLaravelStates();
            })
            ->count();
        //$factoryFile = $factoryCollection->get($this->fullClassName);

        if ($laravelStatesGiven) {
            $withStates = $this->choice("You have defined states in your old factory, do you want to import them to your new factory class?",
                [
                    'Yes',
                    'No',
                ]);

            if ($withStates === 'No') {
                $factoryCollection->withoutStates();
            }
        }

        $createdFactories = (string) $factoryCollection->all()
            ->map(function (FactoryFile $factoryFile) {
                return $factoryFile->modelClass;
            });

        //$this->info("Thank you! $createdFactories was created.");
        //$classPath = config('factories-reloaded.factories_path').'/'.$this->className.'Factory.php';

        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if (( ! $this->hasOption('force') || ! $this->option('force')) && $factoryCollection->atLeastOneFactoryReloadedExists()) {
            //$this->error($this->type.' already exists!');
            $shouldOverwrite = $this->choice("One of the factories already exists. Do you want to overwrite them?", [
                'Yes',
                'No',
            ]);
            if ($shouldOverwrite == 'Yes') {
                $factoryCollection->overwrite();
            }
        }
        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        //$this->makeDirectory($classPath);

        //$this->files->put($classPath, $this->replaceStub());
        $factoryCollection->write();

        if ($factoryCollection->all()
                ->count() === 1) {
            $this->info($factoryCollection->all()
                    ->first()
                    ->getTargetClassName().' created successfully.');
        } else {
            $factoryNames = $factoryCollection->all()
                ->map(function (FactoryFile $factoryFile) {
                    return $factoryFile->getTargetClassName();
                })
                ->implode(',');
            $this->info($factoryNames.'  created successfully.');
        }
    }

    protected function replaceStub(): string
    {
        $extractor = LaravelFactoryExtractor::from($this->fullClassName);

        $uses = '';
        $dummyData = 'return [];';
        $states = '';

        if ($extractor->exists()) {
            $dummyData = $extractor->getDefinitions();
            $states = $extractor->getStates();
            $uses = $extractor->getUses();
        }

        return $this->sortImports(str_replace([
            '{{ uses }}',
            '{{ dummyData }}',
            '{{ states }}',
        ], [
            $uses,
            $dummyData,
            $states,
        ], $this->buildClass($this->fullClassName)));
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../stubs/make-factory.stub';
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

        return str_replace([
            'DummyFullModelClass',
            'DummyModelClass',
            'DummyFactory',
        ], [
            $this->fullClassName,
            $this->className,
            $this->className.'Factory',
        ], $stub);
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
            $this->factoriesNamespace,
        ], $stub);

        return $this;
    }
}

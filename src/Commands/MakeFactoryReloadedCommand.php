<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Commands;

use Christophrumpel\LaravelCommandFilePicker\ClassFinder;
use Christophrumpel\LaravelCommandFilePicker\Traits\PicksClasses;
use Christophrumpel\LaravelFactoriesReloaded\FactoryCollection;
use Christophrumpel\LaravelFactoriesReloaded\FactoryFile;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class MakeFactoryReloadedCommand extends Command
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

    protected string $type = 'Factory';

    private string $fullClassName;

    private string $className;

    private string $modelsPath;

    private string $modelFile;

    private string $factoriesPath;

    private string $factoriesNamespace;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->overWriteConfigDependingOnGivenOptions();

        $factoryCollection = FactoryCollection::fromCollection($this->getModelsToCreate());

        if ($this->option('force')) {
            $factoryCollection->overwrite();
        }

        if ($factoryCollection->hasLaravelStates()) {
            $withStates = $this->choice('You have defined states in your old factory, do you want to import them to your new factory class?',
                [
                    'Yes',
                    'No',
                ]);

            if ($withStates === 'No') {
                $factoryCollection->withoutStates();
            }
        }

        if (( ! $this->hasOption('force') || ! $this->option('force')) && $factoryCollection->atLeastOneFactoryReloadedExists()) {
            $shouldOverwrite = $this->choice('One of the factories already exists. Do you want to overwrite them?', [
                'Yes',
                'No',
            ]);

            if ($shouldOverwrite === 'Yes') {
                $factoryCollection->overwrite();
            }
        }

        if ( ! File::exists(Config::get('factories-reloaded.factories_path'))) {
            File::makeDirectory(Config::get('factories-reloaded.factories_path'));
        }

        $factoryCollection->write();

        if ($factoryCollection->all()
                ->count() === 1) {
            $this->info($factoryCollection->all()
                    ->first()
                    ->getTargetClassFullName().' created successfully.');
        } else {
            $factoryNames = $factoryCollection->all()
                ->map(function (FactoryFile $factoryFile) {
                    return $factoryFile->getTargetClassName();
                })
                ->implode(', ');
            $this->info($factoryNames.' were created successfully under the '.Config::get('factories-reloaded.factories_namespace').' namespace.');
        }
    }

    protected function getModelsToCreate(): Collection
    {
        if ($this->argument('model')) {
            $classFinder = new ClassFinder(new Filesystem());

            return $modelsToCreate = collect([['name' => $classFinder->getFullyQualifiedClassNameFromFile(config('factories-reloaded.models_path').'/'.$this->argument('model').'.php')]]);
        }

        return $modelsToCreate = $this->askToPickModels(config('factories-reloaded.models_path'));

    }

    protected function overWriteConfigDependingOnGivenOptions(): void
    {
        Config::set('factories-reloaded.models_path',
            $this->option('models_path') ?? config('factories-reloaded.models_path'));
        Config::set('factories-reloaded.factories_path',
            $this->option('factories_path') ?? config('factories-reloaded.factories_path'));
        Config::set('factories-reloaded.factories_namespace',
            $this->option('factories_namespace') ?? config('factories-reloaded.factories_namespace'));
    }
}

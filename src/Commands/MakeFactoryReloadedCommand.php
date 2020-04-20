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

    protected FactoryCollection $factoryCollection;

    protected string $className;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->overWriteConfigDependingOnGivenOptions();

        $this->factoryCollection = FactoryCollection::fromCollection($this->getModelsToCreate());

        if ($this->option('force')) {
            $this->factoryCollection->overwrite();
        }

        $this->askAboutLaravelStatesIfGiven();

        $this->aksAboutOverwritingFactoriesIfNeeded();

        if ($this->factoryCollection->write()->isEmpty()) {
            $this->info('No Files created.');

            return;
        }

        $this->showSuccessMessage();
    }

    protected function getModelsToCreate(): Collection
    {
        if ($this->argument('model')) {
            $classFinder = new ClassFinder(new Filesystem());

            return collect([['name' => $classFinder->getFullyQualifiedClassNameFromFile(config('factories-reloaded.models_paths')[0].'/'.$this->argument('model').'.php')]]);
        }

        return $this->askToPickModelsFromMultipleDirectories(config('factories-reloaded.models_paths'));
    }

    protected function overWriteConfigDependingOnGivenOptions(): void
    {
        Config::set(
            'factories-reloaded.models_paths',
            $this->option('models_path') ?  [$this->option('models_path')] : config('factories-reloaded.models_paths')
        );
        Config::set(
            'factories-reloaded.factories_path',
            $this->option('factories_path') ?? config('factories-reloaded.factories_path')
        );
        Config::set(
            'factories-reloaded.factories_namespace',
            $this->option('factories_namespace') ?? config('factories-reloaded.factories_namespace')
        );

    }

    protected function askAboutLaravelStatesIfGiven(): void
    {
        if (! $this->factoryCollection->hasLaravelStates()) {
            return;
        }
        $message = $this->factoryCollection->all()->count() > 1 ? 'You have defined states in your old factories, do you want to import them to your new factory classes?': 'You have defined states in your old factory, do you want to import them to your new factory class?';
        $withStates = $this->choice($message, [
            'No',
            'Yes',
        ]);

        if ($withStates === 'No') {
            $this->factoryCollection->withoutStates();
        }
    }

    protected function aksAboutOverwritingFactoriesIfNeeded(): void
    {
        if (! $this->factoryCollection->atLeastOneFactoryReloadedExists()) {
            return;
        }

        if ($this->option('force')) {
            return;
        }

        $message = $this->factoryCollection->all()->count() > 1 ? 'One of the factories already exists. Do you want to overwrite them?' : 'This factory class already exists. Do you want to overwrite it?';

        $shouldOverwrite = $this->choice($message, [
            'No',
            'Yes',
        ]);

        if ($shouldOverwrite === 'Yes') {
            $this->factoryCollection->overwrite();
        }
    }

    protected function showSuccessMessage(): void
    {
        if ($this->factoryCollection->all()
                ->count() === 1) {
            $this->info($this->factoryCollection->all()
                    ->first()
                    ->getTargetClassFullName().' created successfully.');
        } else {
            $factoryNames = $this->factoryCollection->all()
                ->map(function (FactoryFile $factoryFile) {
                    return $factoryFile->getTargetClassName();
                })
                ->implode(', ');
            $this->info($factoryNames.' were created successfully under the '.Config::get('factories-reloaded.factories_namespace').' namespace.');
        }
    }
}

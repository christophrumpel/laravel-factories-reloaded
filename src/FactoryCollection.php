<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Christophrumpel\LaravelCommandFilePicker\ClassFinder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class FactoryCollection
{
    protected Collection $factoryFiles;

    protected bool $overwrite = false;

    public function __construct()
    {
        $this->factoryFiles = collect();
    }

    public static function fromModels(array $specificModels = []): self
    {
        $factoryCollection = new static();
        $factoryCollection->buildFromModels($specificModels);

        return $factoryCollection;
    }

    public static function fromCollection(Collection $collection): self
    {
        return static::fromModels($collection->transform(fn ($item) => $item['name'])
            ->toArray());
    }

    public function all(): Collection
    {
        return $this->factoryFiles;
    }

    public function write(): Collection
    {
        if (! File::exists(Config::get('factories-reloaded.factories_path'))) {
            File::makeDirectory(Config::get('factories-reloaded.factories_path'));
        }

        return $this->factoryFiles->filter(fn (FactoryFile $factoryFile) => $factoryFile->write($this->overwrite));
    }

    public function withoutStates(): self
    {
        $this->factoryFiles->each->withoutStates();

        return $this;
    }

    public function overwrite(): self
    {
        $this->overwrite = true;

        return $this;
    }

    public function get(string $modelClass): FactoryFile
    {
        return $this->factoryFiles->firstWhere('modelClass', $modelClass);
    }

    public function atLeastOneFactoryReloadedExists(): bool
    {
        return $this->factoryFiles->filter->factoryReloadedExists()
            ->isNotEmpty();
    }

    public function hasLaravelStates(): bool
    {
        return $this->factoryFiles->filter->hasLaravelStates()
            ->isNotEmpty();
    }

    protected function buildFromModels(array $models = []): void
    {
        $this->factoryFiles = collect($models)
            ->whenEmpty(function () {
                $classFinder = new ClassFinder(new Filesystem());

                return collect(Config::get('factories-reloaded.models_paths'))->transform(fn (
                    string $path
                ) => $classFinder->getModelsInDirectory($path)
                    ->transform(fn ($item) => $item['name']));
            })
            ->flatten()
            ->transform(fn (string $model) => FactoryFile::forModel($model));
    }
}

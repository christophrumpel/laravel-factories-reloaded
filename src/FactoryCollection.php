<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Christophrumpel\LaravelCommandFilePicker\ClassFinder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class FactoryCollection
{

    private Collection $factoryFiles;

    private bool $withoutStates = false;

    private bool $overwrite = false;

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

    private function buildFromModels(array $specificModels = []): void
    {
        $classFinder = new ClassFinder(new Filesystem());
        $models = empty($specificModels) ? $classFinder->getModelsInDirectory(config('factories-reloaded.models_path')) : collect($specificModels)->transform(function (
            $modelClass
        ) {
            return ['name' => $modelClass];
        });

        $this->factoryFiles = collect($models)->transform(function (array $modelData) {
            return FactoryFile::forModel($modelData['name']);
        });
    }

    public function all(): Collection
    {
        return $this->factoryFiles;
    }

    public function write(): self
    {
        $this->factoryFiles->each(function (FactoryFile $factoryFile) {
            if ($this->withoutStates) {
                $factoryFile->withoutStates();
            }

            $factoryFile->write($this->overwrite);
        });

        return $this;
    }

    public function withoutStates(): self
    {
        $this->withoutStates = true;

        return $this;
    }

    public function overwrite()
    {
        $this->overwrite = true;

        return $this;
    }

    public function get(string $modelClass): FactoryFile
    {
        return $this->factoryFiles->filter(function (FactoryFile $factoryFile) use ($modelClass) {
            return $modelClass === $factoryFile->modelClass;
        })
            ->first();
    }

    public function atLeastOneFactoryReloadedExists(): bool
    {
        return (bool) $this->factoryFiles->filter(function (FactoryFile $factoryFile) {
            return $factoryFile->factoryReloadedExists();
        })->count();
    }
}
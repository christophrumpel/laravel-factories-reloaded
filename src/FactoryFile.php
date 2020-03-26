<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FactoryFile
{
    public string $modelClass;

    protected bool $hasLaravelFactory;

    protected string $defaults = 'return [];';

    protected string $states = '';

    protected bool $withStates = true;

    protected string $uses = '';

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $extractor = LaravelFactoryExtractor::from($this->modelClass);

        if ($this->hasLaravelFactory = $extractor->exists()) {
            $this->defaults = $extractor->getDefinitions();
            $this->states = $extractor->getStates();
            $this->uses = $extractor->getUses();
        }
    }

    public static function forModel(string $modelClass): FactoryFile
    {
        return new static($modelClass);
    }

    public function defaultFactoryExists(): bool
    {
        return $this->hasLaravelFactory;
    }

    public function hasLaravelStates(): bool
    {
        return $this->states !== '';
    }

    public function write($force = false): void
    {
        if (! $force && $this->factoryReloadedExists()) {
            return;
        }

        File::put($this->getTargetClassPath(), $this->render());
    }

    public function factoryReloadedExists(): bool
    {
        return File::exists($this->getTargetClassPath());
    }

    public function getTargetClassPath(): string
    {
        return config('factories-reloaded.factories_path') . DIRECTORY_SEPARATOR . $this->getTargetClassName() . '.php';
    }

    public function render(): string
    {
        return Str::of($this->getStub())
            ->replace('DummyNamespace', config('factories-reloaded.factories_namespace'))
            ->replace('DummyFullModelClass', $this->modelClass)
            ->replace('DummyModelClass', class_basename($this->modelClass))
            ->replace('DummyFactory', $this->getTargetClassName())
            ->replace('{{ uses }}', $this->uses)
            ->replace('{{ dummyData }}', $this->defaults)
            ->replace('{{ states }}', $this->withStates ? $this->states : '')
            ->replaceMatches('/(?P<imports>(?:use [^;]+;$\n?)+)/m', function($match) {
                return Str::of($match['imports'])->trim()->explode("\n")->sort()->implode("\n");
            });
    }

    public function getTargetClassName(): string
    {
        return class_basename($this->modelClass) . 'Factory';
    }

    public function getTargetClassFullName(): string
    {
        return config('factories-reloaded.factories_namespace') . '\\' . $this->getTargetClassName();
    }

    public function withoutStates(): FactoryFile
    {
        $this->withStates = false;

        return $this;
    }

    protected function getStub()
    {
        return File::get(__DIR__ . '/stubs/make-factory.stub');
    }
}

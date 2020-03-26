<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Illuminate\Support\Facades\File;

class FactoryFile
{
    public string $modelClass;

    protected bool $hasLaravelFactory;

    protected string $defaults = 'return [];';

    protected bool $withDefaults = true;

    protected string $states = '';

    protected bool $withStates = true;

    protected string $uses = '';

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $this->parse();
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

    protected function parse(): void
    {
        $extractor = LaravelFactoryExtractor::from($this->modelClass);

        if ($this->hasLaravelFactory = $extractor->exists()) {
            $this->defaults = $extractor->getDefinitions();
            $this->states = $extractor->getStates();
            $this->uses = $extractor->getUses();
        }
    }

    public function factoryReloadedExists(): bool
    {
        // todo: maybe use laravel's filesystem
        return file_exists($this->getTargetClassPath());
    }

    public function write($force = false): void
    {
        if ($this->factoryReloadedExists() && ! $force) {
            return;
        }

        File::put($this->getTargetClassPath(), $this->render());
    }

    public function getTargetClassPath(): string
    {
        return config('factories-reloaded.factories_path').DIRECTORY_SEPARATOR.class_basename($this->modelClass).'Factory.php';
    }

    public function getTargetClassName(): string
    {
        return class_basename($this->modelClass).'Factory';
    }

    public function getTargetClassFullName(): string
    {
        return config('factories-reloaded.factories_namespace').'\\'.class_basename($this->modelClass).'Factory.php';
    }

    public function withoutStates(): FactoryFile
    {
        $this->withStates = false;

        return $this;
    }

    public function render(): string
    {
        return $this->sortImports(str_replace([
            '{{ uses }}',
            '{{ dummyData }}',
            '{{ states }}',
        ], [
            $this->uses,
            $this->defaults,
            $this->withStates ? $this->states : '',
        ], $this->buildClass($this->modelClass)));
    }

    protected function sortImports($stub)
    {
        if (preg_match('/(?P<imports>(?:use [^;]+;$\n?)+)/m', $stub, $match)) {
            $imports = explode("\n", trim($match['imports']));

            sort($imports);

            return str_replace(trim($match['imports']), implode("\n", $imports), $stub);
        }

        return $stub;
    }

    protected function buildClass($name)
    {
        $stub = File::get($this->getStub());

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace([
            'DummyNamespace',
        ], [
            config('factories-reloaded.factories_namespace'),
        ], $stub);

        return $this;
    }

    protected function replaceClass($stub, $name)
    {
        //formally parent
        $class = str_replace($this->getNamespace($name).'\\', '', $name);
        $stub = str_replace(['DummyClass', '{{ class }}', '{{class}}'], $class, $stub);

        return str_replace(['DummyFullModelClass', 'DummyModelClass', 'DummyFactory'],
            [$this->modelClass, class_basename($this->modelClass), class_basename($this->modelClass) . 'Factory'], $stub);
    }

    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    protected function getStub()
    {
        return __DIR__ . '/stubs/make-factory.stub';
    }
}

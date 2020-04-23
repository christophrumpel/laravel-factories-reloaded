<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use ReflectionFunction;
use SplFileObject;

class LaravelFactoryExtractor
{
    protected ?array $uses = null;

    protected string $className;

    protected ObjectPrybar $factory;

    public function __construct(string $className)
    {
        $this->className = $className;
        $this->factory = new ObjectPrybar(Factory::construct(app(Generator::class), config('factories-reloaded.vanilla_factories_path')));
    }

    public static function from(string $className): self
    {
        return new static($className);
    }

    public function exists(): bool
    {
        return file_exists($this->vanillaFactoryPath());
    }

    protected function vanillaFactoryPath(): string
    {
        return config('factories-reloaded.vanilla_factories_path') . '/' . class_basename($this->className) . 'Factory.php';
    }

    public function getUses(): string
    {
        return collect($this->uses)->map(function ($use) {
            if (in_array($use['class'], ['Faker\\Generator', $this->className], true)) {
                return;
            }

            if ($use['class'] === $use['as']) {
                return 'use ' . $use['class'] . ';';
            }

            return 'use ' . $use['class'] . ' as ' . $use['as'] . ';';
        })->filter()->implode("\n");
    }

    public function getDefinitions(): string
    {
        $definition = collect(
            $this->factory->getProperty('definitions')
        )->get($this->className);

        return ltrim(
            collect(
                $this->getClosureContent($definition instanceof \Closure ? $definition : $definition['default'])
            )->implode('    ')
        );
    }

    protected function getClosureContent(callable $closure): array
    {
        $reflFunc = new ReflectionFunction($closure);
        $this->parseUseStatements($reflFunc);
        $startline = $reflFunc->getStartLine();
        $endline = $reflFunc->getEndLine() - 1;

        $closureContent = collect();
        $file = new SplFileObject($reflFunc->getFileName());
        $file->seek($startline);
        for ($i = $endline; $i > $startline; $i--) {
            $closureContent->push($file->current());
            $file->next();
        }

        return $closureContent->toArray();
    }

    /**
     * @see https://gist.github.com/Zeronights/7b7d90fcf8d4daf9db0c
     * @param $reflection
     */
    protected function parseUseStatements($reflection)
    {
        if ($this->uses !== null) {
            return;
        }

        $source = file_get_contents($reflection->getFileName());

        $tokens = token_get_all($source);

        $builtNamespace = '';
        $buildingNamespace = false;
        $matchedNamespace = false;

        $useStatements = [];
        $record = false;
        $currentUse = [
            'class' => '',
            'as' => '',
        ];

        foreach ($tokens as $token) {
            if ($token[0] === T_NAMESPACE) {
                $buildingNamespace = true;

                if ($matchedNamespace) {
                    break;
                }
            }

            if ($buildingNamespace) {
                if ($token === ';') {
                    $buildingNamespace = false;

                    continue;
                }

                switch ($token[0]) {

                    case T_STRING:
                    case T_NS_SEPARATOR:
                        $builtNamespace .= $token[1];

                        break;
                }

                continue;
            }

            if ($token === ';' || ! is_array($token)) {
                if ($record) {
                    $useStatements[] = $currentUse;
                    $record = false;
                    $currentUse = [
                        'class' => '',
                        'as' => '',
                    ];
                }

                continue;
            }

            if ($token[0] === T_CLASS) {
                break;
            }

            if (strcasecmp($builtNamespace, $reflection->getNamespaceName()) === 0) {
                $matchedNamespace = true;
            }

            if ($matchedNamespace) {
                if ($token[0] === T_USE) {
                    $record = 'class';
                }

                if ($token[0] === T_AS) {
                    $record = 'as';
                }

                if ($record) {
                    switch ($token[0]) {

                        case T_STRING:
                        case T_NS_SEPARATOR:

                            if ($record) {
                                $currentUse[$record] .= $token[1];
                            }

                            break;
                    }
                }
            }
        }


        // Make sure the as key has the name of the class even
        // if there is no alias in the use statement.
        foreach ($useStatements as &$useStatement) {
            if (empty($useStatement['as'])) {
                $useStatement['as'] = basename($useStatement['class']);
            }
        }

        $this->uses = $useStatements;
    }

    public function getStates(): string
    {
        $states = collect($this->factory->getProperty('states'));

        if (! $states->has($this->className)) {
            return '';
        }

        return collect($states->get($this->className))->map(function ($closure, $state) {
            throw_if(
                ! is_callable($closure),
                new \RuntimeException('One of your factory states is defined as an array. It must be of the type closure to import it.')
            );

            $lines = collect($this->getClosureContent($closure))->filter()->map(fn ($item) => str_replace("\n", '', $item));
            $firstLine = $lines->shift();
            $lastLine = $lines->pop();

            if (Str::startsWith(ltrim($firstLine), 'return')) {
                if ($lastLine === null) {
                    $firstLine = str_replace('];', ']);', $firstLine);
                } else {
                    $lines->push(str_replace('];', ']);', $lastLine));
                }
                $lines->push('}');

                return $lines->prepend([
                    '',
                    'public function ' . $this->getStateMethodName($state) . '(): ' . class_basename($this->className) . 'Factory',
                    '{',
                    str_replace('return ', 'return tap(clone $this)->overwriteDefaults(', $firstLine),
                ])->toArray();
            }

            return collect([
                '',
                'public function ' . $this->getStateMethodName($state) . '(): ' . class_basename($this->className) . 'Factory',
                '{',
                '    return tap(clone $this)->overwriteDefaults(function() {',
                '    ' . $firstLine,
            ])->merge($lines->map(fn ($line) => '    ' . $line))->merge([
                '    ' . $lastLine,
                '    });',
                '}',
            ]);
        })->flatten()->map(function ($line) {
            if (ltrim($line) === '') {
                return '';
            }

            return '    ' . $line;
        })->implode("\n");
    }

    protected function getStateMethodName(string $state): string
    {
        return lcfirst(Str::studly($state));
    }
}

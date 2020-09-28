<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Faker\Generator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use ReflectionFunction;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use SplFileObject;

class LaravelFactoryExtractor
{

    protected ?array $uses = null;

    protected string $className;

    protected $factory;

    public function __construct(string $className)
    {
        $this->className = $className;
        //$classNameForFactory = 'ExampleApp\Group';
        $factoryName = Factory::resolveFactoryName(class_basename($className));

        if (class_exists($factoryName)) {
            $this->factory = Factory::factoryForModel(class_basename($className));
        }

        //$this->factory = new ObjectPrybar(Factory::construct(app(Generator::class), config('factories-reloaded.vanilla_factories_path')));
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
        return config('factories-reloaded.vanilla_factories_path').'/'.class_basename($this->className).'Factory.php';
    }

    public function getUses(): string
    {
        return collect($this->uses)
            ->map(function ($use) {
                if (in_array($use['class'], [
                    'Faker\\Generator',
                    $this->className,
                ], true)) {
                    return;
                }

                if ($use['class'] === $use['as']) {
                    return 'use '.$use['class'].';';
                }

                return 'use '.$use['class'].' as '.$use['as'].';';
            })
            ->filter()
            ->implode("\n");
    }

    public function getDefinitions(): string
    {
        $classInfo = (new \Roave\BetterReflection\BetterReflection())->classReflector()
            ->reflect(get_class($this->factory));

        return $classInfo->getMethod('definition')
            ->getBodyCode();
        //$definition = collect(
        //    $this->factory->getProperty('definitions')
        //)->get($this->className);
        //
        //return ltrim(
        //    collect(
        //        $this->getClosureContent($definition instanceof \Closure ? $definition : $definition['default'])
        //    )->implode('    ')
        //);
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
     *
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
        $factoryReflection = (new BetterReflection())->classReflector()
            ->reflect(get_class($this->factory));

        $factoryFileName = $factoryReflection->getFileName();

        $factoryMethods = $factoryReflection->getMethods();

        return collect($factoryMethods)
            ->filter(function (ReflectionMethod $method) use ($factoryFileName) {
                return Str::of($method->getBodyCode())
                        ->contains('$this->state(') && $method->getFileName() === $factoryFileName;
            })
            ->map(function (ReflectionMethod $method) {
                $bodyLines = Str::of($method->getBodyCode())
                    ->explode(";\n");

                $body = collect($bodyLines)
                    ->filter(fn($line) => ! empty($line))
                    ->map(function ($line) {
                        if (Str::of($line)
                            ->contains('return $this->state(')) {
                            return (string) Str::of($line)
                                ->replace('return $this->state(', '    return tap(clone $this)->overwriteDefaults(');
                        }

                        return '    '.$line.";\n\n";
                    })
                    ->implode('');

                return "\n    ".$this->getMethodVisibility($method)." function ".$method->getName()."(): ".class_basename($this->className) . 'Factory'."\n    {\n    $body\n    }";
            })
            ->implode("\n");

        return $states;

        $states = collect($this->factory->getProperty('states'));

        if ( ! $states->has($this->className)) {
            return '';
        }

        return collect($states->get($this->className))
            ->map(function ($closure, $state) {
                $lines = collect($this->getClosureContent($closure))
                    ->filter()
                    ->map(fn($item) => str_replace("\n", '', $item));
                $firstLine = $lines->shift();
                $lastLine = $lines->pop();

                if (Str::startsWith(ltrim($firstLine), 'return')) {
                    if ($lastLine === null) {
                        $firstLine = Str::replaceLast('];', ']);', $firstLine);
                    } else {
                        $lines->push(Str::replaceLast('];', ']);', $lastLine));
                    }
                    $lines->push('}');

                    return $lines->prepend([
                        '',
                        'public function '.$this->getStateMethodName($state).'(): '.class_basename($this->className).'Factory',
                        '{',
                        Str::replaceFirst('return ', 'return tap(clone $this)->overwriteDefaults(', $firstLine),
                    ])
                        ->toArray();
                }

                return collect([
                    '',
                    'public function '.$this->getStateMethodName($state).'(): '.class_basename($this->className).'Factory',
                    '{',
                    '    return tap(clone $this)->overwriteDefaults(function() {',
                    '    '.$firstLine,
                ])
                    ->merge($lines->map(fn($line) => '    '.$line))
                    ->merge([
                        '    '.$lastLine,
                        '    });',
                        '}',
                    ]);
            })
            ->flatten()
            ->map(function ($line) {
                if (ltrim($line) === '') {
                    return '';
                }

                return '    '.$line;
            })
            ->implode("\n");
    }

    protected function getStateMethodName(string $state): string
    {
        return lcfirst(Str::studly($state));
    }

    private function getMethodVisibility(ReflectionMethod $method): string
    {
        if ($method->isPrivate()) {
            return 'private';
        }

        if ($method->isProtected()) {
            return 'protected';
        }

        return 'public';
    }
}

<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use ReflectionFunction;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use SplFileObject;

class LaravelFactoryExtractor
{
    protected ?array $uses = null;

    protected string $className;

    protected $factory;

    protected $vanillaFactoriesNamespace;

    protected $laravelFactoriesNamespace = 'Database\Factories';

    public function __construct(string $className)
    {
        $this->className = $className;
        //$classNameForFactory = 'ExampleApp\Group';

        $this->vanillaFactoriesNamespace = config('factories-reloaded.vanilla_factories_namespace', $this->laravelFactoriesNamespace);

        $this->resolveFactory($className);
    }

    public function resolveFactory($className): void
    {
        $factoryName = $this->resolveFactoryName($className);

        if (class_exists($factoryName)) {
            $this->factory = $factoryName::new();
        }
    }

    public function resolveFactoryName($className): string
    {
        $modelName = class_basename($className);

        return ($this->isDefaultFactoriesNamespace())
            ? Factory::resolveFactoryName($modelName)
            : $this->vanillaFactoriesNamespace . '\\' . $modelName . 'Factory';
    }

    public function isDefaultFactoriesNamespace(): bool
    {
        return $this->vanillaFactoriesNamespace == $this->laravelFactoriesNamespace;
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
        $classInfo = (new BetterReflection())->classReflector()
            ->reflect(get_class($this->factory));

        return $classInfo->getMethod('definition')
            ->getBodyCode();
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

                            $currentUse[$record] .= $token[1];

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
            ->filter(fn (ReflectionMethod $factoryMethod) => $this->isLaravelStateMethod($factoryMethod, $factoryFileName))
            ->map(function (ReflectionMethod $method) {
                // Transform method to new Factory Reloaded Overwrite Defaults method
                $methodBody = $method->getBodyCode();

                // Replace Laravel state method with overwrite method
                $newMethodBody = Str::of($methodBody)
                    ->replace('return $this->state(', '        return tap(clone $this)->overwriteDefaults(');

                // If the method body contains multiple lines, format them
                $lines = explode(PHP_EOL, $newMethodBody);
                if (count($lines) > 1) {
                    $newMethodBody = $this->formatMultipleLinesFactoryMethod($newMethodBody);
                }

                // Put new method body in method
                return "\n    ".$this->getMethodVisibility($method)." function ".$method->getName()."(): ".class_basename($this->className).'Factory'."\n    {\n$newMethodBody\n    }";
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

    private function isLaravelStateMethod(ReflectionMethod $factoryMethod, string $factoryFileName): bool
    {
        return Str::of($factoryMethod->getBodyCode())
                ->contains('$this->state(') && $factoryMethod->getFileName() === $factoryFileName;
    }

    private function formatMultipleLinesFactoryMethod(Stringable $newMethodBody): string
    {
        $lineBefore = '';

        return collect(explode(PHP_EOL, $newMethodBody))
            ->map(function ($line) use (&$lineBefore) {
                $prependSpaces = '        ';
                // Add indention if the line before opens a function
                if (Str::of($lineBefore)
                    ->endsWith('{')) {
                    $prependSpaces .= '    ';
                }

                $lineBefore = $line;

                return Str::of($line)
                    ->ltrim(' ')
                    ->prepend($prependSpaces);
            })
            ->map(function ($line, $key) use (&$lineBefore) {
                if ($key === 0) {
                    $lineBefore = '';
                }

                if ($key !== 0 && ! Str::of($lineBefore)
                        ->endsWith('{') && Str::of($line)
                        ->ltrim(' ')
                        ->startsWith('return')) {
                    $line = Str::of($line)
                        ->prepend(PHP_EOL);
                }

                $lineBefore = $line;

                return $line;
            })
            ->implode(PHP_EOL);
    }
}

<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Illuminate\Support\Collection;

abstract class BaseFactory
{

    protected $className;

    public static function build()
    {

        $className = (new static())->className;
        $data = (new static)->getData();

        return new $className($data);
    }

    public static function createMultiple(int $count): Collection
    {
        if ( ! $count) {
            throw new \InvalidArgumentException('Please provide a count bigger than zero.');
        }
        $className = (new static())->className;

        return collect(range(1, $count))->transform(function () use ($className) {
            $data = (new static)->getData();

            return new $className($data);
        });
    }
}

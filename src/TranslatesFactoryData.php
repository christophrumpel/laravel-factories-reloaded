<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Illuminate\Database\Eloquent\FactoryBuilder;

trait TranslatesFactoryData
{
    private static function isFactory($item): bool
    {
        return $item instanceof BaseFactory || $item instanceof FactoryBuilder;
    }

    private function prepareModelData(array $defaultModelFields): array
    {
        return collect($defaultModelFields)
            ->map(function ($item) {
                if ($this->isFactory($item)) {
                    return $item->create()->id;
                }

                return $item;
            })
            ->toArray();
    }
}

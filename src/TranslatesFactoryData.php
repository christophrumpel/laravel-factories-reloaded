<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Illuminate\Database\Eloquent\FactoryBuilder;

trait TranslatesFactoryData
{

    private static function isFactory($item): bool
    {
        return $item instanceof BaseFactory || $item instanceof FactoryBuilder;
    }

    private function prepareModelData(string $creationType, array $defaultModelFields): array
    {
        return collect($defaultModelFields)
            ->map(function ($item) use ($creationType) {
                if ($this->isFactory($item)) {
                    return $creationType === 'create' ? $item->create()->id : null;
                }

                return $item;
            })
            ->toArray();
    }
}

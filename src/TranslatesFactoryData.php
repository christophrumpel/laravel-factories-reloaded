<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Illuminate\Database\Eloquent\Factories\Factory;

trait TranslatesFactoryData
{
    private static function isFactory($item): bool
    {
        return $item instanceof Factory || $item instanceof BaseFactory;
    }

    private static function isCallable($field): bool
    {
        return is_callable($field) && ! is_string($field) && ! is_array($field);
    }

    private function transformModelFields(array $defaultModelFields): array
    {
        foreach ($defaultModelFields as &$field) {
            $field = $this->transformField($field, $defaultModelFields);
        }

        return $defaultModelFields;
    }

    private function transformField($field, array $defaultModelFields)
    {
        if ($this->isFactory($field)) {
            return $field->create()->getKey();
        }

        if ($this->isCallable($field)) {
            return $field($defaultModelFields);
        }

        return $field;
    }
}

<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests\Factories\CustomNamespace
;

use Christophrumpel\LaravelFactoriesReloaded\BaseFactory;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\CustomNamespace\NamespacedModel;
use Faker\Generator;

class NamespacedModelFactory extends BaseFactory
{

    /**
     * @var string
     */
    protected $modelClass = NamespacedModel::class;

    public function create(array $extra = []): NamespacedModel
    {
        return parent::build($extra);
    }

    public function make(array $extra = []): NamespacedModel
    {
        return parent::build($extra, 'make');
    }

    public function getData(Generator $faker): array
    {
        return [
            'name' => $faker->name,
            'description' => 'Our family lasagne recipe.'
        ];
    }

}

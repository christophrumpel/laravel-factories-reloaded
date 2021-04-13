<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests;

use Christophrumpel\LaravelFactoriesReloaded\LaravelFactoryExtractor;
use Illuminate\Support\Facades\Config;

class LaravelFactoryExtractorTest extends TestCase
{
    /** @test
     * @dataProvider factoryNameProvider
     */
    public function it_can_resolve_factory_name($className, $vanillaFactoriesNamespace, $expectedFactoryName): void
    {
        Config::set('factories-reloaded.vanilla_factories_namespace', $vanillaFactoriesNamespace);

        $extractor = $this->getMockBuilder(LaravelFactoryExtractor::class)
            ->setConstructorArgs([$className])
            ->onlyMethods([])
            ->getMock();

        $this->assertSame($expectedFactoryName, $extractor->resolveFactoryName($className));
    }

    public function factoryNameProvider()
    {
        return [
            [
                "App\Models\Zoo\Animal",
                "Database\Factories\Zoo",
                "Database\Factories\Zoo\AnimalFactory",
            ],
            [
                "App\Models\Zoo\Animal",
                "Database\Factories",
                "Database\Factories\AnimalFactory",
            ],
            [
                "App\Models\Animal",
                "Database\Factories\Zoo",
                "Database\Factories\Zoo\AnimalFactory",
            ],
            [
                "App\Models\Zoo\Animal",
                "Database\Factories\Creature",
                "Database\Factories\Creature\AnimalFactory",
            ],
        ];
    }
}

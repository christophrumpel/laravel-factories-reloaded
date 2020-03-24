<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests;


use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Group;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Recipe;
use Illuminate\Support\Str;

class FactoryFileTest extends TestCase
{

    /** @test * */
    public function it_can_be_created_from_static_method(): void
    {
        $groupFactoryFile = FactoryFile::forModel(Group::class);

        $this->assertInstanceOf(FactoryFile::class, $groupFactoryFile);
    }

    /** @test * */
    public function it_tells_if_a_laravel_factory_exist(): void
    {
        $groupFactoryFile = FactoryFile::forModel(Group::class);

        $this->assertTrue($groupFactoryFile->hasLaravelFactory());
    }

    /** @test * */
    public function it_tells_if_a_laravel_factory_has_states_defined(): void
    {
        $groupFactoryFile = FactoryFile::forModel(Group::class);

        $this->assertTrue($groupFactoryFile->hasLaravelStates());
    }

    /** @test * */
    public function it_tells_if_a_reloaded_factory_class_already_exists(): void
    {
        $groupFactoryFile = FactoryFile::forModel(Group::class);

        $this->assertTrue($groupFactoryFile->exists());
    }

    /** @test * */
    public function it_writes_factory(): void
    {
        $groupFactoryFile = FactoryFile::forModel(Group::class);

        $groupFactoryFile->write();

        $this->assertFileExists(__DIR__ . '/../Factories/tmp/GroupFactory.php');
    }

    /** @test * */
    public function it_can_ignore_states(): void
    {
        $recipeFactoryFile = FactoryFile::forModel(Recipe::class);

        $recipeFactoryFile->withoutStates();

        $recipeFactoryFile->write();

        $this->assertFileExists(__DIR__ . '/../Factories/tmp/RecipeFactory.php');

        $generatedRecipeFactoryContent = file_get_contents(__DIR__ . '/../Factories/tmp/RecipeFactory.php');

        $this->assertFalse(Str::containsAll($generatedRecipeFactoryContent, [
            'public function withGroup',
            'public function withDifferentGroup'
        ]));
    }
}

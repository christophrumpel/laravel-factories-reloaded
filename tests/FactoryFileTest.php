<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests;

use Christophrumpel\LaravelFactoriesReloaded\FactoryFile;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Group;
use Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Ingredient;
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

        $this->assertTrue($groupFactoryFile->defaultFactoryExists());
    }

    /** @test * */
    public function it_tells_if_a_laravel_factory_has_states_defined(): void
    {
        $groupFactoryFile = FactoryFile::forModel(Group::class);
        $recipeFactoryFile = FactoryFile::forModel(Recipe::class);

        $this->assertFalse($groupFactoryFile->hasLaravelStates());
        $this->assertTrue($recipeFactoryFile->hasLaravelStates());
    }

    /** @test * */
    public function it_tells_if_a_reloaded_factory_class_already_exists(): void
    {
        $groupFactoryFile = FactoryFile::forModel(Group::class);
        $groupFactoryFile->write();

        $this->assertTrue($groupFactoryFile->factoryReloadedExists());
    }

    /** @test * */
    public function it_writes_factory(): void
    {
        $ingredientFactoryFile = FactoryFile::forModel(Ingredient::class);

        $ingredientFactoryFile->write();

        $this->assertFileExists($ingredientFactoryFile->getTargetClassPath());
    }

    /** @test * */
    public function it_can_overwrite_an_existing_factory(): void
    {
        $ingredientFactoryFile = FactoryFile::forModel(Recipe::class);
        $ingredientFactoryFile->write();
        $originalContent = file_get_contents($ingredientFactoryFile->getTargetClassPath());

        $ingredientFactoryFile->withoutStates()->write(true);

        $this->assertNotSame($originalContent, file_get_contents($ingredientFactoryFile->getTargetClassPath()));
    }

    /** @test * */
    public function it_can_add_default_states(): void
    {
        $recipeFactoryFile = FactoryFile::forModel(Recipe::class);

        $content = $recipeFactoryFile->render();

        $this->assertTrue(Str::containsAll($content, [
            'public function withGroup',
            'public function withDifferentGroup',
        ]));
    }

    /** @test * */
    public function it_can_ignore_states(): void
    {
        $recipeFactoryFile = FactoryFile::forModel(Recipe::class);

        $content = $recipeFactoryFile->withoutStates()->render();

        $this->assertFalse(Str::containsAll($content, [
            'public function withGroup',
            'public function withDifferentGroup',
        ]));
    }

    /** @test **/
    public function it_gives_factory_path()
    {
        $recipeFactoryFile = FactoryFile::forModel(Recipe::class);
        $this->assertEquals(__DIR__.'/tmp/RecipeFactory.php', $recipeFactoryFile->getTargetClassPath());
    }

    /** @test **/
    public function it_gives_factory_class_full_name()
    {
        $recipeFactoryFile = FactoryFile::forModel(Recipe::class);
        $this->assertEquals('Christophrumpel\LaravelFactoriesReloaded\Tests\Factories\RecipeFactory.php', $recipeFactoryFile->getTargetClassFullName());
    }

    /** @test **/
    public function it_gives_factory_class_name()
    {
        $recipeFactoryFile = FactoryFile::forModel(Recipe::class);
        $this->assertEquals('RecipeFactory', $recipeFactoryFile->getTargetClassName());
    }
}

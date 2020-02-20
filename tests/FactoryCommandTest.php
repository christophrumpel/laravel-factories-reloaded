<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;

class FactoryCommandTest extends TestCase
{

    /** @test */
    public function it_fails_if_no_models_found()
    {
        $this->expectException(\LogicException::class);

        // Set to a path with no models given
        Config::set('factories-reloaded.models_path', __DIR__.'/');

        $this->artisan('make:factory-reloaded');
    }

    /** @test */
    public function it_creates_factory_for_chosen_model()
    {
        $this->artisan('make:factory-reloaded')
            ->expectsQuestion('Please pick a model',
                '<href=file://'.__DIR__.'/Models/Group.php>Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Group</>')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(__DIR__.'/Factories/tmp/GroupFactory.php'));
    }

    /** @test **/
    public function it_replaces_the_the_dummy_code_in_the_new_factory_class()
    {
        $this->artisan('make:factory-reloaded')
            ->expectsQuestion('Please pick a model',
                '<href=file://'.__DIR__.'/Models/Group.php>Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Group</>')
            ->assertExitCode(0);

        $generatedFactoryContent = file_get_contents(__DIR__.'/Factories/tmp/GroupFactory.php');

        $this->assertTrue(Str::containsAll($generatedFactoryContent, [
            'GroupFactory',
            'Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Group',
            'Group',
            'create(',
            'make(',
        ],));
    }
}

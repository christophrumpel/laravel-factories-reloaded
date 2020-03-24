<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests;


use Christophrumpel\LaravelCommandFilePicker\ClassFinder;
use Christophrumpel\LaravelFactoriesReloaded\LaravelFactoryExtractor;
use Illuminate\Filesystem\Filesystem;

class LaravelFactoryExtractorTest extends TestCase
{

    /** @var ClassFinder  */
    private ClassFinder $classFinder;

    public function setUp(): void
    {
        parent::setUp();
        $this->classFinder = new ClassFinder(new Filesystem());
    }

    /** @test **/
    public function it_can_be_created_through_static_factory()
    {
        $className = $this->classFinder->getFullyQualifiedClassNameFromFile(__DIR__.'/Models/Group.php');
        $extractor = LaravelFactoryExtractor::from($className);

        $this->assertInstanceOf(LaravelFactoryExtractor::class, $extractor);
    }

    /** @test **/
    public function it_parses_the_provided_class()
    {
        $className = $this->classFinder->getFullyQualifiedClassNameFromFile(__DIR__.'/Models/Group.php');
        $extractor = LaravelFactoryExtractor::from($className);

        // method to parse the given class so that we have the data prepared
        $extractor->parseGivenClass();

        // check if the given data is correct
        $this->assertEquals([''], $extractor->getUses());
        $this->assertEquals([''], $extractor->getDefinitions());
        $this->assertEquals([''], $extractor->getStates());
    }

    /** @test **/
    public function it_tells_if_states_are_given()
    {
        $groupClass = $this->classFinder->getFullyQualifiedClassNameFromFile(__DIR__.'/Models/Group.php');
        $groupExtractor = LaravelFactoryExtractor::from($groupClass);

        $groupExtractor->parseGivenClass();

        $this->assertFalse($groupExtractor->hasStates());

        $recipeClass = $this->classFinder->getFullyQualifiedClassNameFromFile(__DIR__.'/Models/Recipe.php');
        $recipeExtractor = LaravelFactoryExtractor::from($recipeClass);

        $recipeExtractor->parseGivenClass();

        $this->assertTrue($recipeExtractor->hasStates());
    }
}

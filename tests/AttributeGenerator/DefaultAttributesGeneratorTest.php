<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests\AttributeGenerator;

use Christophrumpel\LaravelFactoriesReloaded\AttributeGenerator\DefaultAttributesGenerator;
use Christophrumpel\LaravelFactoriesReloaded\AttributeGenerator\TableColumnTypeReader;
use ExampleApp\Models\Group;

class DefaultAttributesGeneratorTest extends AttributeGeneraterBaseTestCase
{
    protected $tableColumnTypeReaderStub;

    public function columnTypesToFakerProvider()
    {
        return collect(self::$columnTypesToFakerMap)->map(fn($type, $column) => [$column, $type])->values()->all();
    }

    public function columnNamesToFakerProvider()
    {
        return collect(self::$columnTypesToFakerMap)->map(fn($type, $column) => [$column, $type])->values()->all();
    }

    /**
     * @dataProvider columnTypesToFakerProvider
     * @test
     */
    public function it_generates_correct_fake_data_for_column_type(string $name, string $expected)
    {
        $generator = new DefaultAttributesGenerator(Group::class);
        $definitions = $generator->getDefinitions();

        // ignore whitespace, we just care about getting it right
        $expected = preg_replace('/\s/', '', $expected);
        $actual = preg_replace('/\s/', '', $definitions->get($name));

        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider columnNamesToFakerProvider
     * @test
     */
    public function it_generates_correct_fake_data_for_column_name(string $name, string $expected)
    {
        $generator = new DefaultAttributesGenerator(Group::class);
        $definitions = $generator->getDefinitions();

        // ignore whitespace, we just care about getting it right
        $expected = preg_replace('/\s/', '', $expected);
        $actual = preg_replace('/\s/', '', $definitions->get($name));

        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function it_generates_code_block_correctly()
    {
        $this->withoutExceptionHandling();
        $generator = new DefaultAttributesGenerator(Group::class);
        $codeBlock = $generator->getDefinitionsCodeBlock();

        $expected = collect(self::$columnTypesToFakerMap)
            ->map(fn($fakedValue, $column) => "'$column' => $fakedValue")
            ->join(",\n            ");
        $expected = "return [\n            $expected\n        ];";

        $this->assertSame($expected, $codeBlock);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->tableColumnTypeReaderStub = $this->createStub(TableColumnTypeReader::class);

        $this->tableColumnTypeReaderStub->method('getColumnTypeMap')
            ->willReturn(collect(self::$columnTypeMap));

        $this->swap(TableColumnTypeReader::class, $this->tableColumnTypeReaderStub);
    }
}

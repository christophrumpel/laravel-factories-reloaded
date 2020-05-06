<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests\AttributeGenerator;

use Christophrumpel\LaravelFactoriesReloaded\AttributeGenerator\TableColumnTypeReader;
use Doctrine\DBAL\DBALException;

class TableColumnTypeReaderTest extends AttributeGeneraterBaseTestCase
{
    /** @test */
    public function it_gets_the_correct_column_type_map()
    {
        $this->app->bind('db.connection', function () {
            $connectionMock = \Mockery::mock('connection');
            $builderMock = \Mockery::mock('builder');

            $connectionMock->shouldReceive('getSchemaBuilder')
                ->andReturn($builderMock);

            $builderMock->shouldReceive('getColumnListing')
                ->andReturn(array_keys(self::$columnTypeMap));

            foreach (self::$columnTypeMap as $column => $type) {
                $builderMock->shouldReceive('getColumnType')
                    ->with('fake_table', $column)
                    ->andReturn($type);
            }

            return $connectionMock;
        });

        $columnsTypeMap = app(TableColumnTypeReader::class)->getColumnTypeMap('fake_table');

        $this->assertEquals(self::$columnTypeMap, $columnsTypeMap->toArray());
    }

    /** @test */
    public function it_returns_unknown_type_when_column_type_is_not_supported()
    {
        $this->app->bind('db.connection', function () {
            $connectionMock = \Mockery::mock('connection');
            $builderMock = \Mockery::mock('builder');

            $connectionMock->shouldReceive('getSchemaBuilder')
                ->andReturn($builderMock);

            $builderMock->shouldReceive('getColumnListing')
                ->andReturn(array_keys(self::$columnTypeMap));
            $builderMock->shouldReceive('getColumnType')
                ->andThrow(DBALException::class);

            return $connectionMock;
        });

        $columnsTypeMap = app(TableColumnTypeReader::class)->getColumnTypeMap('fake_table');

        $expected = collect(self::$columnTypeMap)->map(fn($type) => TableColumnTypeReader::UNKNOWN_TYPE);
        $this->assertEquals($expected->toArray(), $columnsTypeMap->toArray());
    }
}

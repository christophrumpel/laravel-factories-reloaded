<?php

namespace Christophrumpel\LaravelFactoriesReloaded\AttributeGenerator;

use Doctrine\DBAL\DBALException;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;

class TableColumnTypeReader
{
    public const UNKNOWN_TYPE = 'unknown';

    public function getColumnTypeMap(string $table): Collection
    {
        $schemaBuilder = app('db.connection')->getSchemaBuilder();

        return collect($schemaBuilder->getColumnListing($table))
            ->mapWithKeys(function ($column) use ($schemaBuilder, $table) {
                try {
                    return [$column => $schemaBuilder->getColumnType($table, $column)];
                } catch (DBALException $exception) {
                    return [$column => self::UNKNOWN_TYPE];
                }
            });
    }
}

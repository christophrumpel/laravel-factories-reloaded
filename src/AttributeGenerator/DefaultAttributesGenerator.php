<?php

namespace Christophrumpel\LaravelFactoriesReloaded\AttributeGenerator;

use Doctrine\DBAL\Types\Types;
use Illuminate\Support\Collection;

class DefaultAttributesGenerator
{
    protected Collection $definitions;

    protected $fakeableTypes = [
        //Types::ARRAY => '',
        Types::BIGINT => '$faker->randomNumber()',
        //Types::BINARY => '',
        //Types::BLOB => '',
        Types::BOOLEAN => '$faker->boolean',
        Types::DATE_MUTABLE => '$faker->date()',
        Types::DATE_IMMUTABLE => '$faker->date()',
        //Types::DATEINTERVAL => '',
        Types::DATETIME_MUTABLE => '$faker->dateTime()',
        Types::DATETIME_IMMUTABLE => '$faker->dateTime()',
        Types::DATETIMETZ_MUTABLE => '$faker->dateTime()',
        Types::DATETIMETZ_IMMUTABLE => '$faker->dateTime()',
        Types::DECIMAL => '$faker->randomFloat()',
        Types::FLOAT => '$faker->randomFloat()',
        Types::GUID => '$faker->word',
        Types::INTEGER => '$faker->randomNumber()',
        Types::JSON => "json_encode([
            'first_name' => \$faker->firstName,
            'last_name' => \$faker->lastName,
            'company' => \$faker->company,
        ])",
        //Types::OBJECT => '',
        //Types::SIMPLE_ARRAY => '',
        Types::SMALLINT => '$faker->randomNumber()',
        Types::STRING => '$faker->word',
        Types::TEXT => '$faker->text',
        Types::TIME_MUTABLE => '$faker->time()',
        Types::TIME_IMMUTABLE => '$faker->time()',
    ];

    protected $fakeableNames = [
        'city' => '$faker->city',
        'company' => '$faker->company',
        'country' => '$faker->country',
        'created_at' => '\Illuminate\Support\Carbon::now()',
        'description' => '$faker->text',
        'email' => '$faker->safeEmail',
        'first_name' => '$faker->firstName',
        'firstname' => '$faker->firstName',
        'guid' => '$faker->uuid',
        'last_name' => '$faker->lastName',
        'lastname' => '$faker->lastName',
        'lat' => '$faker->latitude',
        'latitude' => '$faker->latitude',
        'lng' => '$faker->longitude',
        'longitude' => '$faker->longitude',
        'name' => '$faker->name',
        'password' => '\Hash::make($faker->password)',
        'phone' => '$faker->phoneNumber',
        'phone_number' => '$faker->phoneNumber',
        'postcode' => '$faker->postcode',
        'postal_code' => '$faker->postcode',
        'remember_token' => '\Str::random(10)',
        'slug' => '$faker->slug',
        'street' => '$faker->streetName',
        'address1' => '$faker->streetAddress',
        'address2' => '$faker->secondaryAddress',
        'summary' => '$faker->text',
        'updated_at' => '\Illuminate\Support\Carbon::now()',
        'url' => '$faker->url',
        'user_name' => '$faker->userName',
        'username' => '$faker->userName',
        'uuid' => '$faker->uuid',
        'zip' => '$faker->postcode',
    ];

    /** @var \Illuminate\Database\Eloquent\Model */
    protected $model;

    public function __construct(string $modelClass)
    {
        $this->model = app($modelClass);
        $table = $this->getTableForModel($modelClass);
        $this->definitions = $this->generateDefinitionsFromTable($table);
    }

    public function getDefinitions()
    {
        return $this->definitions;
    }

    public function getDefinitionsCodeBlock(): string
    {
        // all the extra spaces are needed for formatting
        return rtrim(collect([
            "return [",
            $this->definitions->map(fn($item, $key) => "            '$key' => $item")
                ->implode(",\n"),
            '        ];',
        ])->implode("\n"));
    }

    protected function generateDefinitionsFromTable($table)
    {
        return app(TableColumnTypeReader::class)
            ->getColumnTypeMap($table)
            ->filter(function ($type, $column) {
                // get rid of auto incrementing ids
                if ($this->model->incrementing && ($column === $this->model->getKeyName())) {
                    return false;
                }

                return true;
            })
            ->map(fn($type, $column) => $this->getColumnDefinition($column, $type));
    }

    protected function getColumnDefinition(string $name, string $type)
    {
        $definition = collect($this->fakeableNames)->get($name);

        $definition ??= collect($this->fakeableTypes)->get($type);

        $definition ??= '$faker->word';

        return $definition;
    }

    protected function getTableForModel(string $modelClass)
    {
        $model = app($modelClass);

        return $model->getConnection()
                ->getTablePrefix().$model->getTable();
    }
}

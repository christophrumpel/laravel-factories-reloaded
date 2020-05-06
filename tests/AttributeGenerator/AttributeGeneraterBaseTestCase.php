<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests\AttributeGenerator;

use Christophrumpel\LaravelFactoriesReloaded\Tests\TestCase;
use Doctrine\DBAL\Types\Types;
use Illuminate\Foundation\Testing\Concerns\InteractsWithContainer;

class AttributeGeneraterBaseTestCase extends TestCase
{
    use InteractsWithContainer;

    public static array $columnTypeMap = [
        //'array_column' => Types::ARRAY,
        'bigint_column' => Types::BIGINT,
        //'binary_column' => Types::BINARY,
        //'blob_column' => Types::BLOB,
        'boolean_column' => Types::BOOLEAN,
        'date_mutable_column' => Types::DATE_MUTABLE,
        'date_immutable_column' => Types::DATE_IMMUTABLE,
        //'dateinterval_column' => Types::DATEINTERVAL,
        'datetime_mutable_column' => Types::DATETIME_MUTABLE,
        'datetime_immutable_column' => Types::DATETIME_IMMUTABLE,
        'datetimetz_mutable_column' => Types::DATETIMETZ_MUTABLE,
        'datetimetz_immutable_column' => Types::DATETIMETZ_IMMUTABLE,
        'decimal_column' => Types::DECIMAL,
        'float_column' => Types::FLOAT,
        'guid_column' => Types::GUID,
        'integer_column' => Types::INTEGER,
        'json_column' => Types::JSON,
        //'object_column' => Types::OBJECT,
        //'simple_array_column' => Types::SIMPLE_ARRAY,
        'smallint_column' => Types::SMALLINT,
        'string_column' => Types::STRING,
        'text_column' => Types::TEXT,
        'time_mutable_column' => Types::TIME_MUTABLE,
        'time_immutable_column' => Types::TIME_IMMUTABLE,
    ];

    public static $columnTypesToFakerMap = [
        // 'array_column' => '',
        'bigint_column' => '$faker->randomNumber()',
        // 'binary_column' => '',
        // 'blob_column' => '',
        'boolean_column' => '$faker->boolean',
        'date_mutable_column' => '$faker->date()',
        'date_immutable_column' => '$faker->date()',
        // 'dateinterval_column' => '',
        'datetime_mutable_column' => '$faker->dateTime()',
        'datetime_immutable_column' => '$faker->dateTime()',
        'datetimetz_mutable_column' => '$faker->dateTime()',
        'datetimetz_immutable_column' => '$faker->dateTime()',
        'decimal_column' => '$faker->randomFloat()',
        'float_column' => '$faker->randomFloat()',
        'guid_column' => '$faker->word',
        'integer_column' => '$faker->randomNumber()',
        'json_column' => "json_encode([
            'first_name' => \$faker->firstName,
            'last_name' => \$faker->lastName,
            'company' => \$faker->company,
        ])",
        // 'object_column' => '',
        // 'simple_array_column' => '',
        'smallint_column' => '$faker->randomNumber()',
        'string_column' => '$faker->word',
        'text_column' => '$faker->text',
        'time_mutable_column' => '$faker->time()',
        'time_immutable_column' => '$faker->time()',
    ];

    public static $columnNamesToFakerMap = [
        'city' => '$faker->city',
        'company' => '$faker->company',
        'country' => '$faker->country',
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
        'password' => 'bcrypt($faker->password)',
        'phone' => '$faker->phoneNumber',
        'phone_number' => '$faker->phoneNumber',
        'postcode' => '$faker->postcode',
        'postal_code' => '$faker->postcode',
        'remember_token' => 'Str::random(10)',
        'slug' => '$faker->slug',
        'street' => '$faker->streetName',
        'address1' => '$faker->streetAddress',
        'address2' => '$faker->secondaryAddress',
        'summary' => '$faker->text',
        'url' => '$faker->url',
        'user_name' => '$faker->userName',
        'username' => '$faker->userName',
        'uuid' => '$faker->uuid',
        'zip' => '$faker->postcode',
    ];
}

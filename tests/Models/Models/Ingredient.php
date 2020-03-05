<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests\Models\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = ['name', 'description'];
}

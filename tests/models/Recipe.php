<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = ['name', 'description', 'published'];
}

<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests\Models\CustomNamespace;

use Illuminate\Database\Eloquent\Model;

class NamespacedModel extends Model
{
    protected $fillable = ['name', 'description'];
}

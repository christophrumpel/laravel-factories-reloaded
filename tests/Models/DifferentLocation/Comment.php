<?php

namespace Christophrumpel\LaravelFactoriesReloaded\Tests\Models\DifferentLocation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{

    protected $fillable = [
        'content',
    ];

}

<?php

namespace ExampleApp\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recipe extends Model
{
    protected $fillable = ['name', 'description', 'group_id'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}

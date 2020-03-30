<?php

namespace ExampleApp\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = ['name', 'description', 'group_id'];
}

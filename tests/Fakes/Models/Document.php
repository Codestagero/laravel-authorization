<?php

namespace Codestage\Authorization\Tests\Fakes\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'user_id'
    ];
}
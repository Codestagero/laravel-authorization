<?php

namespace Codestage\Authorization\Tests\Fakes\Models;

use Codestage\Authorization\Traits\HasPermissions;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasPermissions;
}

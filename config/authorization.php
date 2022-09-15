<?php

use Codestage\Authorization\Contracts\IPermissionEnum;

return [

    /*
    |--------------------------------------------------------------------------
    | Permissions Enum
    |--------------------------------------------------------------------------
    |
    | The enum which contains this application's permissions.
    | MUST implement Codestage\Authorization\Contracts\IPermissionEnum.
    |
    */
    'permissions_enum' => IPermissionEnum::class
];

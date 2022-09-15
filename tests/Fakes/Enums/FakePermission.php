<?php

namespace Codestage\Authorization\Tests\Fakes\Enums;

use Codestage\Authorization\Contracts\IPermissionEnum;

enum FakePermission: string implements IPermissionEnum
{
    case ExamplePermission = 'example.test';
}

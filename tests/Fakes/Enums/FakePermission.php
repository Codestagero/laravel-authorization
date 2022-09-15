<?php

namespace Codestage\Authorization\Tests\Fakes\Enums;

use Codestage\Authorization\Contracts\IPermissionEnum;

enum FakePermission: string implements IPermissionEnum
{
    case ExamplePermission1 = 'example.test';
    case ExamplePermission2 = 'example.anotherTest';
    case ExamplePermission3 = 'example.thisIsNotATest';
}

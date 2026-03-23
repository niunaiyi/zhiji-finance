<?php

namespace App\Ship\Tests;

use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Tests\TestCase as ParentTestCase;

class ShipTestCase extends ParentTestCase
{
    protected function getTestingUser(): User
    {
        return User::factory()->create();
    }
}

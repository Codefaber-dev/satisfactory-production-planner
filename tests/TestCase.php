<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function actingAsUser()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        return $this;
    }
}

<?php

namespace Tests;

use App\Models\Recipe;
use App\Models\User;
use App\Production\Step;
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

<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Automatically run the database seeders after migrations when using
     * the RefreshDatabase testing trait. This ensures tests always start
     * with the seeded migration data as requested.
     *
     * Set the default seeder class here (Database\Seeders\DatabaseSeeder).
     * Individual tests may override by setting the $seed or $seeder property.
     *
     * @var bool
     */
    protected $seed = true;

    /**
     * The seeder class to run when seeding the test database.
     *
     * @var string
     */
    protected $seeder = \Database\Seeders\DatabaseSeeder::class;
}

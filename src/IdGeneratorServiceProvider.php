<?php

namespace SayaWRT\Unik;

use Illuminate\Support\ServiceProvider;

/**
 * Service Provider For IdGenerator
 * @since 1.0.0
 */
class IdGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Boot logic if needed
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->make(IdGenerator::class);
    }
}

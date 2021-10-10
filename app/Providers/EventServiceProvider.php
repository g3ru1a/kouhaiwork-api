<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\ExampleEvent::class => [
            \App\Listeners\ExampleListener::class,
        ],
        'Illuminate\Cache\Events\KeyForgotten' => [
            'App\Listeners\CacheEOLListener',
        ],
        // \Illuminate\Cache\Events\CacheHit::class => [
        //     \App\Listeners\CacheEOLListener::class,
        // ],
    ];

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return true;
    }
}

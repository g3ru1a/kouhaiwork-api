<?php

namespace App\Listeners;

use App\Events\CacheEOLEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\CacheHit;

class CacheEOLListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  KeyForgotten  $event
     * @return void
     */
    public function handle(KeyForgotten $event)
    {
        $folder = public_path('/storage/cache/pages/' . $event->key);
        if(is_dir($folder)){
            Log::info("Deleting Pages from cache key: " . $event->key);
            Log::info("Deleting folder: " . $folder);
            array_map('unlink', glob("$folder/*.*"));
            rmdir($folder);
        }
    }
}

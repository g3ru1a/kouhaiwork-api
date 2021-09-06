<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
    public function boot()
    {

        Validator::extend('max_file_array', function ($attribute, $value, $parameters, $validator) {

            $total_size = array_reduce($value, function ($sum, $item) {
                // each item is UploadedFile Object
                $sum += filesize($item->path());
                return $sum;
            });

            // $parameters[0] in kilobytes
            return $total_size < $parameters[0] * 1024;
        });
    }
}

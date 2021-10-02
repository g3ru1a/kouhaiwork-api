<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use PHPUnit\TextUI\XmlConfiguration\Php;

$version = env('APP_VERSION', 'nan');

$router->group(['prefix' => '/v' . $version], function () use ($router) {

    /*
    |   AUTHENTICATION ROUTES
    */
    $router->group(['prefix' => '/auth'], function () use ($router) {
            $router->post('/login', 'AuthController@login');
            $router->post('/register', 'AuthController@register');
            $router->post('/verify/{user_id}/{token}', 'AuthController@verifyEmail');
            $router->post('/password/request/', 'AuthController@forgotPasswordRequest');
            $router->post('/password/reset/', 'AuthController@resetPassword');
            $router->post('/logout', 'AuthController@logout');
            $router->post('/check', ['middleware'=>'auth', 'uses' => 'AuthController@check']);
        }
    );
});

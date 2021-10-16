<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use PHPUnit\TextUI\XmlConfiguration\Php;

$version = env('APP_VERSION', 'nan');

$router->group(['prefix' => '/v' . $version . '/admins', 'middleware' => ['auth', 'rank3']], function () use ($router) {

    $router->get('/users/all/{search}', 'UserController@searchAll');
    $router->get('/users/{search}', 'UserController@searchR3');
    /*
    |   ANNOUNCEMENTS ROUTES
    */
    $router->group(['prefix' => '/announcements'], function () use ($router) {
        $router->post('/', 'AnnouncementsController@store');
        $router->put('/{id}', 'AnnouncementsController@update');
        $router->delete('/{id}', 'AnnouncementsController@delete');
    });
});

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
        $router->post('/check', ['middleware' => 'auth', 'uses' => 'AuthController@check']);
    });

    /*
    |   MANGA ROUTES
    */
    $router->group(['prefix' => '/manga'], function () use ($router) {
        /** Information */
        $router->get('/latest', 'MangaController@latest');
        $router->get('/all', 'MangaController@all');
        $router->get('/week', 'MangaController@week');
        $router->post('/search', 'MangaController@search');
        $router->get('/get/{id}', 'MangaController@get');

        /** Option Getters */
        $router->get('/search/parameters', 'MangaOptionsController@searchParams');
        $router->get('/genres', 'GenreController@index');
        $router->get('/themes', 'ThemeController@index');
        $router->get('/demographics', 'DemographicController@index');
        $router->get('/authors', 'AuthorController@index');
        $router->get('/artists', 'ArtistController@index');
    });

    /*
    |   ANNOUNCEMENTS ROUTES
    */
    $router->get('/announcements', 'AnnouncementsController@index');
});

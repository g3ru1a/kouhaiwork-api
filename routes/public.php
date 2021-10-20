<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use PHPUnit\TextUI\XmlConfiguration\Php;

$version = env('APP_VERSION', 'nan');

$router->group(['prefix' => '/v' . $version], function () use ($router) {
    $router->get('/detachs3', 'MediaController@detachS3');
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
    |   SEARCH ROUTES
    */
    $router->group(['prefix' => '/search'], function () use ($router) {
        /** Information */
        $router->post('/manga', 'SearchController@manga');
        $router->get('/parameters', 'SearchController@mangaParams');

        $router->get('/users/r2/{search}', 'UserController@searchR2');
    });

    /*
    |   CHAPTERS ROUTES
    */
    $router->group(['prefix' => '/chapters'], function () use ($router) {
        /** Information */
        $router->get('/latest', 'ChapterController@latest');
        $router->get('/recent', 'ChapterController@recent');
        $router->get('/get/{id}', 'ChapterController@get');
    });

    /*
    |   MANGA ROUTES
    */
    $router->group(['prefix' => '/manga'], function () use ($router) {
        /** Information */
        $router->get('/recent', 'MangaController@recent');
        $router->get('/all', 'MangaController@all');
        $router->get('/get/{id}', 'MangaController@get');
        $router->get('/chapters/{id}', 'MangaController@getChapters');

        /** Option Getters */
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

<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use PHPUnit\TextUI\XmlConfiguration\Php;

$version = env('APP_VERSION', 'nan');

$router->get('/storage/{filename}', 'MediaController@getFile');
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
        $router->post('/logout', ['middleware' => 'auth', 'uses' => 'AuthController@logout']);
        $router->post('/check', ['middleware' => 'auth', 'uses' => 'AuthController@check']);
    });

    /*
    |   CHAPTER ROUTES
    */
    $router->group(['prefix' => '/chapters'], function () use ($router) {
        $router->get('/manga/{manga_id}', 'ChapterController@getMangaChapters');
        $router->get('/latest', 'ChapterController@latest');
        $router->get('/recent', 'ChapterController@recent');
        $router->get('/{id}', 'ChapterController@get');
    });

    /*
    |   MANGA ROUTES
    */
    $router->group(['prefix' => '/manga'], function () use ($router) {
        /** Getters */
        $router->get('/', 'MangaController@index');
        $router->get('/recent', 'MangaController@getHasChapterRecent');
        $router->get('/get/{id}', 'MangaController@getHasChaptersWithEverything');

        $router->post('/search', 'SearchController@searchManga');

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

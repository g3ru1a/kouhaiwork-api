<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use PHPUnit\TextUI\XmlConfiguration\Php;

$version = env('APP_VERSION', 'nan');

$router->group(['prefix' => '/v' . $version . '/groups', 'middleware' => ['auth', 'rank2']], function () use ($router) {

    /*
    |   USER'S GROUPS
    */
    $router->group(['prefix' => '/me'], function () use ($router) {

        $router->get('/', 'GroupController@index');
        $router->post('/', 'GroupController@store');
        $router->post('/{id}/banner', 'GroupController@storeBanner');
        $router->put('/{id}', 'GroupController@update');
        $router->delete('/{id}', 'GroupController@delete');

        $router->get('/where/{position}', 'GroupController@getWhere');

        $router->get('/{id}/members', 'GroupController@getMembers');
        $router->post('/{id}/members', 'GroupController@addMembers');
        $router->delete('/{id}/members', 'GroupController@kickMembers');

        $router->post('/{id}/leave', 'GroupController@leaveGroup');
    });

    /*
    |   CHAPTERS ROUTES
    */
    $router->group(['prefix' => '/chapters'], function () use ($router) {
        /** Genres */
        $router->post('/search', 'SearchController@chapters');
        $router->get('/{id}', 'ChapterController@getEdit');
        $router->post('/pages/{id}', 'ChapterController@addPages');
        $router->post('/', 'ChapterController@store');
        $router->put('/{id}', 'ChapterController@update');
        $router->delete('/{id}', 'ChapterController@delete');
    });

    /*
    |   MANGA ROUTES
    */
    $router->group(['prefix' => '/mangas'], function () use ($router) {
        /** Genres */
        $router->get('/all', 'MangaController@allEdit');
        $router->get('/all/upload', 'MangaController@allUpload');
        $router->get('/{id}', 'MangaController@getEdit');
        $router->post('/', 'MangaController@store');
        $router->put('/{id}', 'MangaController@update');
        $router->delete('/{id}', 'MangaController@delete');
    });

    /*
    |   MANGA DATA
    */
    $router->group(['prefix' => '/manga'], function () use ($router) {
        /** Genres */
        $router->post('/genres', 'GenreController@store');
        $router->put('/genres/{id}', 'GenreController@update');
        $router->delete('/genres/{id}', 'GenreController@delete');

        /** Themes */
        $router->post('/themes', 'ThemeController@store');
        $router->put('/themes/{id}', 'ThemeController@update');
        $router->delete('/themes/{id}', 'ThemeController@delete');

        /** Demographics */
        $router->post('/demographics', 'DemographicController@store');
        $router->put('/demographics/{id}', 'DemographicController@update');
        $router->delete('/demographics/{id}', 'DemographicController@delete');

        /** Authors */
        $router->post('/authors', 'AuthorController@store');
        $router->put('/authors/{id}', 'AuthorController@update');
        $router->delete('/authors/{id}', 'AuthorController@delete');

        /** Artists */
        $router->post('/artists', 'ArtistController@store');
        $router->put('/artists/{id}', 'ArtistController@update');
        $router->delete('/artists/{id}', 'ArtistController@delete');
    });

});

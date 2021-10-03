<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use PHPUnit\TextUI\XmlConfiguration\Php;

$version = env('APP_VERSION', 'nan');

$router->group(['prefix' => '/v' . $version . '/groups', 'middleware' => ['auth', 'rank2']], function () use ($router) {

        /*
    |   MANGA DATA
    */
    $router->group(['prefix' => '/manga'], function () use ($router) {
        $router->post('/genres', 'GenreController@store');
        $router->put('/genres/{id}', 'GenreController@update');
        $router->delete('/genres/{id}', 'GenreController@delete');

        $router->post('/themes', 'ThemeController@store');
        $router->put('/themes/{id}', 'ThemeController@update');
        $router->delete('/themes/{id}', 'ThemeController@delete');

        $router->post('/demographics', 'DemographicController@store');
        $router->put('/demographics/{id}', 'DemographicController@update');
        $router->delete('/demographics/{id}', 'DemographicController@delete');

        $router->post('/authors', 'AuthorController@store');
        $router->put('/authors/{id}', 'AuthorController@update');
        $router->delete('/authors/{id}', 'AuthorController@delete');

        $router->post('/artists', 'ArtistController@store');
        $router->put('/artists/{id}', 'ArtistController@update');
        $router->delete('/artists/{id}', 'ArtistController@delete');
    });

});

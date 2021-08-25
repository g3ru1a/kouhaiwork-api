<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => '/api'], function () use ($router){

    $router->post('/login', 'AuthController@login');
    $router->post('/register', 'AuthController@register');

    //Manga
    $router->get('/mangas', 'MangaController@index');
    $router->get('/mangas/{id}', 'MangaController@get');
    $router->get('/manga/search/{search}', 'MangaController@search');

    $router->get('/manga/latest', 'MangaController@latest');
    $router->get('/manga/all', 'MangaController@all');
    $router->get('/manga/week', 'MangaController@week');
    //MangaGenre
    $router->get('/manga/genres', 'MangaGenreController@index');
    //MangaThemes
    $router->get('/manga/themes', 'MangaThemeController@index');
    //MangaDemographic
    $router->get('/manga/demographic', 'MangaDemographicController@index');

    $router->group(['middleware' => ['auth']], function () use ($router){
        $router->post('/check', 'AuthController@check');
        $router->post('/logout', 'AuthController@logout');
        
        //Chapters
        $router->post('/chapter/upload', 'ChapterController@upload');

        //Manga
        $router->post('/mangas', 'MangaController@store');
        $router->put('/mangas/{id}', 'MangaController@update');
        $router->delete('/mangas/{id}', 'MangaController@delete');

        //MangaGenre
        $router->post('/manga/genres', 'MangaGenreController@store');
        $router->put('/manga/genres/{id}', 'MangaGenreController@update');
        $router->delete('/manga/genres/{id}', 'MangaGenreController@delete');

        //MangaThemes
        $router->post('/manga/themes', 'MangaThemeController@store');
        $router->put('/manga/themes/{id}', 'MangaThemeController@update');
        $router->delete('/manga/themes/{id}', 'MangaThemeController@delete');

        //MangaDemographic
        $router->post('/manga/demographic', 'MangaDemographicController@store');
        $router->put('/manga/demographic/{id}', 'MangaDemographicController@update');
        $router->delete('/manga/demographic/{id}', 'MangaDemographicController@delete');
    });
});
<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use PHPUnit\TextUI\XmlConfiguration\Php;

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
    return phpinfo();
    return $router->app->version();
});


$router->group(['prefix' => '/v2'], function () use ($router) {
    $router->post('/mail/test', 'MailController@test');

    $router->post('/login', 'AuthController@login');
    $router->post('/register', 'AuthController@register');
    $router->post('/verify/{user_id}/{token}', 'AuthController@verifyEmail');
    $router->post('/password/request/', 'MailController@forgotPasswordRequest');
    $router->post('/password/reset/', 'AuthController@resetPassword');

    $router->get('/announcements', 'PostController@index');

    //Manga
    $router->get('/mangas', 'MangaController@index');
    $router->get('/mangas/{id}', 'MangaController@get');
    $router->post('/manga/search', 'MangaController@search');

    $router->get('/manga/latest', 'MangaController@latest');
    $router->get('/manga/all', 'MangaController@all');
    $router->get('/manga/week', 'MangaController@week');

    $router->get('/mangas/{id}/chapters', 'MangaController@chapters');
    $router->get('/chapters/{id}', 'ChapterController@get');

    //Search Params
    $router->get('/search/parameters', 'MangaOptionsController@searchParams');

    //MangaGenre
    $router->get('/manga/genres', 'MangaGenreController@index');
    //MangaThemes
    $router->get('/manga/themes', 'MangaThemeController@index');
    //MangaDemographic
    $router->get('/manga/demographic', 'MangaDemographicController@index');
    //MangaAuthor
    $router->get('/manga/authors', 'AuthorController@index');
    //MangaArtists
    $router->get('/manga/artists', 'ArtistController@index');

    $router->group(['middleware' => ['auth']], function () use ($router) {
        $router->post('/check', 'AuthController@check');
        $router->post('/logout', 'AuthController@logout');
        $router->get('/users/r1/{search}', 'UserController@searchR1');

        $router->group(['middleware' => ['rank2']], function () use ($router) {
            $router->get('/users/r2/{search}', 'UserController@searchR2');
            //Groups
            $router->get('/me/groups', 'GroupController@getUserGroups');
            $router->get('/me/groups/owner', 'GroupController@getUserOwnedGroups');
            $router->get('/me/groups/member', 'GroupController@getUserMemberGroups');
            $router->get('/me/group/members/{groupID}', 'GroupController@getUserGroupMembers');

            $router->post('/me/groups/kick/', 'GroupController@kickMemberFromGroup');
            $router->post('/me/groups/leave/{id}', 'GroupController@leaveGroup');
            $router->post('/groups/members/add', 'GroupController@addMembers');

            $router->post('/groups/create', 'GroupController@create');
            $router->put('/groups/update/{id}', 'GroupController@update');
            $router->delete('/groups/delete/{id}', 'GroupController@delete');

            //Chapters
            $router->get('/r2/chapter/{id}', 'ChapterController@getChapter');
            $router->post('/chapters/search/', 'ChapterController@search');
            $router->post('/chapter/upload', 'ChapterController@upload');
            $router->post('/chapter/update/{id}', 'ChapterController@update');
            $router->delete('/chapter/delete/{id}', 'ChapterController@delete');


            $router->get('/r2/manga/all', 'MangaController@allR2');
            $router->get('/r2/series/since/{chapter_id}', 'MangaController@allSince');
        });
        $router->group(['middleware' => ['rank3']], function () use ($router) {
            $router->get('/users/r3/{search}', 'UserController@searchR3');
            $router->get('/users/all/{search}', 'UserController@searchAll');

            //Announcements
            $router->post('/announcements', 'PostController@store');
            $router->delete('/announcements/delete/{id}', 'PostController@delete');

            //Manga
            $router->get('/admin/manga/all', 'MangaController@allAdmin');
            $router->get('/admin/mangas/{id}', 'MangaController@getAdmin');
            $router->post('/mangas', 'MangaController@store');
            $router->post('/mangas/{id}', 'MangaController@update');
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

            //Authors
            $router->post('/manga/authors', 'AuthorController@store');
            $router->put('/manga/authors/{id}', 'AuthorController@update');
            $router->delete('/manga/authors/{id}', 'AuthorController@delete');

            //Artists
            $router->post('/manga/artists', 'ArtistController@store');
            $router->put('/manga/artists/{id}', 'ArtistController@update');
            $router->delete('/manga/artists/{id}', 'ArtistController@delete');

        });
    });
});
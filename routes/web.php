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

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

$router->get('/', function () use ($router) {
    return view('index');
});


$router->group(['middleware' => ['cookie_response', 'json_r']], function($router) {
    /**
     * MyAnimeList routs
     */
    $router->group(['prefix' => 'myanimelist', 'namespace' => 'MyAnimeList'], function() use($router) {
        // Auth
        $router->group(['prefix' => 'auth'], function() use($router) {
            $router->get('/authorize', ['as' => 'mal.auth.authorize', 'uses' => 'MalAuthApiController@authorize']);
            $router->get('/authorization', ['as' => 'mal.auth.authorization', 'uses' => 'MalAuthApiController@authorization']);
            $router->get('/authorized', ['as' => 'mal.auth.authorized', 'uses' => 'MalAuthApiController@test']);
            $router->get('/logout', ['as' => 'mal.auth.logout', 'uses' => 'MalAuthApiController@logout']);
        });

        // Api for authorized users
        $router->group(['middleware' => 'mal_auth'], function() use($router) {
            // User endpoints
            $router->group(['prefix' => 'user'], function() use($router) {
                $router->get('/me', ['as' => 'mal.user.me', 'uses' => 'MalUserApiController@me']);
            });

            // Anime List
            $router->group(['prefix' => 'anime'], function() use($router) {
                $router->patch('/rates/update/{anime_id}', ['as' => 'mal.list.rates.update', 'uses' => 'MalAnimeListApiController@updateRates']);
                $router->get('/rates/{anime_id}', ['as' => 'mal.list.rates', 'uses' => 'MalAnimeListApiController@rates']);
                $router->get('/library', ['as' => 'mal.list.all', 'uses' => 'MalAnimeListApiController@library']);
            });
        });
    });


    /**
     * Shikimori routs
     */
    $router->group(['prefix' => 'shikimori', 'namespace' => 'Shikimori'], function() use($router) {
        // Auth
        $router->group(['prefix' => 'auth'], function() use($router) {
            $router->get('/authorize', ['as' => 'shikimori.auth.authorize', 'uses' => 'ShikimoriAuthApiController@authorize']);
            $router->get('/authorization', ['as' => 'shikimori.auth.authorization', 'uses' => 'ShikimoriAuthApiController@authorization']);
            $router->get('/authorized', ['as' => 'shikimori.auth.authorized', 'uses' => 'ShikimoriAuthApiController@authorized']);
            $router->get('/logout', ['as' => 'shikimori.auth.logout', 'uses' => 'ShikimoriAuthApiController@logout']);
        });

        // Api for authorized users
        $router->group(['middleware' => 'shikimori_auth'], function() use($router) {
            // User endpoints
            $router->group(['prefix' => 'user'], function() use($router) {
                $router->get('/me', ['as' => 'shikimori.user.me', 'uses' => 'ShikimoriUserApiController@me']);
            });

            // Anime List
            $router->group(['prefix' => 'anime'], function() use($router) {
                $router->get('/library[/{user_id?}]', ['as' => 'shikimori.list.all', 'uses' => 'ShikimoriAnimeListApiController@library']);
                $router->patch('/rates/update/{rate_id}', ['as' => 'shikimori.list.rates.update', 'uses' => 'ShikimoriAnimeListApiController@updateRates']);
                $router->post('/rates/create', ['as' => 'shikimori.list.rates.new', 'uses' => 'ShikimoriAnimeListApiController@newRates']);
                $router->get('/rates/{rate_id}', ['as' => 'shikimori.list.rates', 'uses' => 'ShikimoriAnimeListApiController@rates']);
            });
        });
    });
});

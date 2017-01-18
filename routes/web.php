<?php

use Illuminate\Http\Request;

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

$app->get('/', function (Request $request) use ($app) {
    return redirect('/app.html');
});

$app->group(['middleware' => 'ajax'], function () use ($app) {
    // Sessions
    $app->post('sessions', 'SessionController@login');
    $app->delete('sessions', 'SessionController@logout');

    // Users
    $app->get('users', 'UserController@list');
    $app->post('users', 'UserController@signup');
    $app->get('users/self', 'UserController@self');

    // Conferences
    $app->get('conferences', 'ConferenceController@list');
    $app->post('conferences', 'ConferenceController@create');
    $app->get('conferences/{id}', 'ConferenceController@detail');
    $app->post('conferences/{id}/chairs', 'ConferenceController@setChairs');

    // Articles
    $app->get('articles', 'ArticleController@list');
    $app->get('articles/{id}', 'ArticleController@detail');
    $app->post('articles', 'ArticleController@create');
    $app->post('articles/{id}/reviewers', 'ArticleController@setReviewers');
    $app->post('articles/{id}/lock', 'ArticleController@lock');
    $app->post('articles/{id}/unlock', 'ArticleController@unlock');

    // Commits
    $app->post('commits', 'CommitController@create');
    $app->post('commits/{id}/review', 'CommitController@review');
});

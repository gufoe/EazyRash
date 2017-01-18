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

$app->group(['middleware' => 'ajax'], function () use ($app) {
    // Sessions
    $app->post('sessions', 'SessionController@login');
    $app->delete('sessions', 'SessionController@logout');

    // Users
    $app->get('users', 'UserController@lists');
    $app->post('users', 'UserController@signup');
    $app->get('users/self', 'UserController@self');

    // Conferences
    $app->get('conferences', 'ConferenceController@lists');
    $app->post('conferences', 'ConferenceController@create');
    $app->get('conferences/{id}', 'ConferenceController@detail');
    $app->post('conferences/{id}/chairs', 'ConferenceController@setChairs');

    // Articles
    $app->get('articles', 'ArticleController@lists');
    $app->get('articles/{id}', 'ArticleController@detail');
    $app->post('articles', 'ArticleController@create');
    $app->post('articles/{id}/reviewers', 'ArticleController@setReviewers');
    $app->post('articles/{id}/lock', 'ArticleController@lock');
    $app->post('articles/{id}/unlock', 'ArticleController@unlock');

    // Commits
    $app->post('commits', 'CommitController@create');
    $app->post('commits/{id}/review', 'CommitController@review');
});

// Any other requests is sent to the frontend
$app->get('{path:.*}', function ($path) use ($app) {
    if (!$path) $path = 'app.html';
    // Sanitize path
    while (strpos('..', $path) !== false)
        $path = str_replace('..', '', $path);
    if (file_exists(base_path("/public/$path")))
    return file_get_contents(base_path("/public/$path"));
});

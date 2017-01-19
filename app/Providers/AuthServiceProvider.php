<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            $token = $request->header('X-Auth-Token');
            if ($token) {
                return User::where('api_token', $token)->first();
            }
        });

        Gate::define('review-article', function (User $user, \App\Article $article) {
            return $article->reviewers()->whereId($user->id)->exists()
                && $article->status == \App\Article::REVIEWING;
        });

        Gate::define('manage-article', function (User $user, \App\Article $article) {
            return $article->conference->chairs()->whereId($user->id)->exists();
        });

        Gate::define('edit-article', function (User $user, \App\Article $article) {
            return $article->user_id == $user->id;
        });

        Gate::define('update-article', function (User $user, \App\Article $article) {
            return Gate::allows('edit-article', $article)
                && $article->status == \App\Article::UPDATING;
        });

        Gate::define('download-article', function (User $user, \App\Article $article) {
            return in_array($article->status, [\App\Article::ACCEPTED, \App\Article::REJECTED]);
        });

        Gate::define('manage-conference', function (User $user, \App\Conference $conference) {
            return $conference->chairs()->whereId($user->id)->exists();
        });
    }
}

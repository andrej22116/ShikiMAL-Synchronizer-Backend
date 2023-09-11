<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Cookie
        $this->app->singleton(\App\Http\Helpers\Cookie::class);

        // AuthModel
        $this->app->singleton(\App\Models\Shikimori\AuthModel::class);
        $this->app->singleton(\App\Models\MyAnimeList\AuthModel::class);

        // UserModel
        $this->app->singleton(\App\Models\Shikimori\UserModel::class);
        $this->app->singleton(\App\Models\MyAnimeList\UserModel::class);

        // AnimeListModel
        $this->app->singleton(\App\Models\Shikimori\AnimeListModel::class);
        $this->app->singleton(\App\Models\MyAnimeList\AnimeListModel::class);
    }
}

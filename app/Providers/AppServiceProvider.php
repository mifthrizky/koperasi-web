<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Session;
use App\Session\MongoSessionHandler;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        // session handler mongodb
        $this->extendMongoSession();
    }

    protected function extendMongoSession()
    {
        Session::extend('mongodb', function ($app) {
            $connection = $app['db']->connection('mongodb')->getMongoClient();
            $database = $connection->selectDatabase(env('DB_DATABASE', 'db_koperasi'));
            $collection = $database->selectCollection(env('SESSION_TABLE', 'sessions'));

            return new MongoSessionHandler($collection, env('SESSION_LIFETIME', 120));
        });
    }
}

<?php

namespace App\Providers;

use App\Models\Barang;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Retur;
use App\Observers\ReturObserver;
use App\Observers\BarangObserver;
use App\Observers\PembelianObserver;
use App\Observers\PenjualanObserver;
use App\Session\MongoSessionHandler;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

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

        Barang::observe(BarangObserver::class);
        Pembelian::observe(PembelianObserver::class);
        Penjualan::observe(PenjualanObserver::class);
        Retur::observe(ReturObserver::class);
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

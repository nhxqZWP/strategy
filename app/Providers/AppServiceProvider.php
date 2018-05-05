<?php

namespace App\Providers;

use App\Services\TradePlatform\HuobiService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton("Binance", function(){
            $key = config('platform.binance.key');
            $secret = config('platform.binance.secret');
            $api = \Binance\API($key,$secret);
            return $api;
        });
        $this->app->singleton("HuoBi", function(){
             $accountId = config('platform.huobi.accountId', '');
             $accessKey = config('platform.huobi.accessKey', '');
             $secretKey = config('platform.huobi.secretKey', '');
             $api = new HuobiService($accountId, $accessKey, $secretKey);
             return $api;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

<?php

namespace App\Services\TradePlatform;

class BinanceService
{
   public static function ping()
   {
       $res = Binance::TestConnectivity();
       return $res;
   }
}
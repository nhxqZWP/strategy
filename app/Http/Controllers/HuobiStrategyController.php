<?php

namespace App\Http\Controllers;

use App\Services\TradePlatform;

class HuobiStrategyController extends Controller
{
     public function getHuobiDepth()
     {
          $huoBi = app('HuoBi');
          $symbols = $huoBi->get_common_symbols();
          dd($symbols);
     }
}
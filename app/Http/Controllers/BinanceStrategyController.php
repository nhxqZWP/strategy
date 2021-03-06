<?php

namespace App\Http\Controllers;

use App\Services\TradePlatform;
use Binance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Khill\Lavacharts\Lavacharts;

class BinanceStrategyController extends Controller
{
     public function getHuobiDepth(Request $request)
     {
          $ticker = $request->get('ticker', 'btcusdt');
          $huoBi = app('HuoBi');
          $depths = $huoBi->get_market_depth($ticker, 'step0');
          $bids = $depths->tick->bids;
          $asks = $depths->tick->asks;

          // 去掉最大量
          $bidsDeal = [];
          $max = 0;
          foreach ($bids as $k => $b1) {
               if ($bids[$k][1] > $max) $max = $bids[$k][1];
          }
          foreach ($bids as $k => $b2) {
               if ($bids[$k][1] >= $max) continue;
               $bidsDeal[$k] = $b2;
          }

          //buy
          $lava = new Lavacharts; // See note below for Laravel
          $stocksTableBuy = $lava->DataTable();  // Lava::DataTable() if using Laravel
          $stocksTableBuy->addNumberColumn('Price')
               ->addNumberColumn('Amount');
          $buyCountAll = count($bidsDeal);
          $buyCount = intval(count($bidsDeal)/2);
          $buyPartOne = 0;
          $buyPartTwo = 0;
          foreach ($bidsDeal as $k => $b) {
               if ($k > $buyCountAll - 10) continue;
               $stocksTableBuy->addRow([
                    $bidsDeal[$k][0], $bidsDeal[$k][1]
               ]);
               if ($k < $buyCount) {
                    $buyPartOne += $bidsDeal[$k][1];
               } else {
                    $buyPartTwo += $bidsDeal[$k][1];
               }
          }
          $lava->ColumnChart('Finances', $stocksTableBuy, [
               'title' => 'market depth (bids)',
               'titleTextStyle' => [
                    'color'    => '#eb6b2c',
                    'fontSize' => 14
               ],
          ]);
          //ask
//          $lava2 = new Lavacharts; // See note below for Laravel
//          $stocksTableAsk = $lava2->DataTable();  // Lava::DataTable() if using Laravel
//          $stocksTableAsk->addNumberColumn('Price')
//               ->addNumberColumn('Amount');
//
//          foreach ($asks as $k => $a) {
//               $stocksTableAsk->addRow([
//                    $asks[$k][0], $asks[$k][1]
//               ]);
//          }
//
//          $lava2->ColumnChart('Finances2', $stocksTableAsk, [
//               'title' => 'market depth (asks)',
//               'titleTextStyle' => [
//                    'color'    => '#eb6b2c',
//                    'fontSize' => 14
//               ],
//          ]);

          return view('depth', ['lava' => $lava, /*'lava2' => $lava2, */'buyOne' => $buyPartOne, 'buyTwo' => $buyPartTwo]);
     }

     public function getAllDepth(Request $request)
     {
          $type = $request->get('type', 1);
          $anaRedis = Redis::get('binance_all_depth');
          if (is_null($anaRedis)) {
//               $api = app('Binance');
               $key = config('platform.binance.key');
               $secret = config('platform.binance.secret');
               $api = new Binance($key,$secret);
               $depths = $api->exchangeInfo();
               dd($depths);
               $data = $depths->data;
               $tickers = [];
               $analysis = [];
               foreach ($data as $d) {
                    $d = (array)$d;
                    array_push($tickers, $d['base-currency'].$d['quote-currency']);
               }
               foreach ($tickers as $ticker) {
                    $analysis[] = TradePlatform\HuobiService::getDepthAnalysis($ticker);
               }
          } else {
               $analysis = json_decode($anaRedis, true);
          }
          switch ($type) {
               case 1 : array_multisort(array_column($analysis,'buy'),SORT_DESC,$analysis);
                    break;
               case 2 : array_multisort(array_column($analysis,'ask'),SORT_ASC,$analysis);
                    break;
               case 3 : array_multisort(array_column($analysis,'del'),SORT_DESC,$analysis);
                    break;
               default : array_multisort(array_column($analysis,'buy'),SORT_DESC,$analysis);
                    break;
          }

          return view('depth_ana', ['analysis' => $analysis]);
     }
}
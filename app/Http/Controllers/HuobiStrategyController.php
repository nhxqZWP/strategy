<?php

namespace App\Http\Controllers;

use App\Services\TradePlatform;
use Illuminate\Http\Request;
use Khill\Lavacharts\Lavacharts;

class HuobiStrategyController extends Controller
{
     public function getHuobiDepth(Request $request)
     {
          $ticker = $request->get('ticker', 'btcusdt');
          $huoBi = app('HuoBi');
          $depths = $huoBi->get_market_depth($ticker, 'step0');
          $bids = $depths->tick->bids;
          $asks = $depths->tick->asks;

          //buy
          $lava = new Lavacharts; // See note below for Laravel
          $stocksTableBuy = $lava->DataTable();  // Lava::DataTable() if using Laravel
          $stocksTableBuy->addNumberColumn('Price')
               ->addNumberColumn('Amount');
          $buyCount = intval(count($bids)/2);
          $buyPartOne = 0;
          $buyPartTwo = 0;
          foreach ($bids as $k => $b) {
               $stocksTableBuy->addRow([
                    $bids[$k][0], $bids[$k][1]
               ]);
               if ($k < $buyCount) {
                    $buyPartOne += $bids[$k][1];
               } else {
                    $buyPartTwo += $bids[$k][1];
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

     public function getAllDepth()
     {
          $huoBi = app('HuoBi');
          $depths = $huoBi->get_common_symbols();
          $data = $depths->data;
          foreach ($data as $d) {
               echo $d->base-currency.$d->quote-currency.'<br/>';
          }
          dd(1);
     }
}
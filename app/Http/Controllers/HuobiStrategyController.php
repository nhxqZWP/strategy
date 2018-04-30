<?php

namespace App\Http\Controllers;

use App\Services\TradePlatform;
use Khill\Lavacharts\Lavacharts;

class HuobiStrategyController extends Controller
{
     public function getHuobiDepth()
     {
          $huoBi = app('HuoBi');
          $depths = $huoBi->get_market_depth('btcusdt', 'step2');
          $bids = $depths->tick->bids;
          $asks = $depths->tick->asks;

          //buy
          $lava = new Lavacharts; // See note below for Laravel
          $stocksTableBuy = $lava->DataTable();  // Lava::DataTable() if using Laravel
          $stocksTableBuy->addNumberColumn('Price')
               ->addNumberColumn('Amount');
          foreach ($bids as $k => $b) {
               $stocksTableBuy->addRow([
                    $bids[$k][0], $bids[$k][1]
               ]);
          }
          $lava->ColumnChart('Finances', $stocksTableBuy, [
               'title' => 'market depth (bids)',
               'titleTextStyle' => [
                    'color'    => '#eb6b2c',
                    'fontSize' => 14
               ],
          ]);
          //ask
          $lava2 = new Lavacharts; // See note below for Laravel
          $stocksTableAsk = $lava2->DataTable();  // Lava::DataTable() if using Laravel
          $stocksTableAsk->addNumberColumn('Price')
               ->addNumberColumn('Amount');

          foreach ($asks as $k => $a) {
               $stocksTableAsk->addRow([
                    $asks[$k][0], $asks[$k][1]
               ]);
          }

          $lava2->ColumnChart('Finances2', $stocksTableAsk, [
               'title' => 'market depth (asks)',
               'titleTextStyle' => [
                    'color'    => '#eb6b2c',
                    'fontSize' => 14
               ],
          ]);

          return view('depth', ['lava' => $lava, 'lava2' => $lava2]);
     }
}
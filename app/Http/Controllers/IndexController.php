<?php

namespace App\Http\Controllers;

use App\Services\TradePlatform\GateIoService;

class IndexController extends Controller
{
    public function getIndex()
    {
        $pair1 = 'gtc_usdt';
        $wallet1 = GateIoService::getPairBalance($pair1);
        $data = [
            'wallet1' => $wallet1
        ];
        return view('welcome', $data);
    }
}
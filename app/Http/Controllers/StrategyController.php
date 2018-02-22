<?php

namespace App\Http\Controllers;

use App\Services\TradePlatform\GateIo;
use App\Services\TradePlatform\GateIoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class StrategyController extends Controller
{
    public function getGateIoOneCoin(Request $request)
    {
        $pair = $request->get('pair');
        if (empty($pair)) return redirect()->back()->withInput()->with('error', '没有选择交易对');

        // 运行状态
        $open = Redis::get('switch_' . $pair);
        if (is_null($open) || $open == 2) $open = 2;
        // 获取当前挂单列表
        $openOrders = GateIo::open_orders($pair);
        // 获取24内成交记录
        $tradeHistory = GateIo::get_my_trade_history_all($pair);
        // 此交易对钱包余额
        $wallet = GateIoService::getPairBalance($pair);
        $data = [
            'openOrders' => $openOrders,
            'tradeHistory' => $tradeHistory,
            'open' => $open,
            'pair' => $pair,
            'wallet' => $wallet
        ];
        return view('coin_analysis_show', $data);
    }

    public function updateRunStatus(Request $request)
    {
        $pair = $request->get('pair');
        $status = $request->get('status');
        Redis::set('switch_' . $pair, $status);
        return redirect()->back();
    }
}
<?php

namespace App\Services\TradePlatform;

// 定义参数
//define('ACCOUNT_ID', ''); // 你的账户ID
//define('ACCESS_KEY', ''); // 你的ACCESS_KEY
//define('SECRET_KEY', ''); // 你的SECRET_KEY

use Illuminate\Support\Facades\Redis;

class HuobiService {
//     private $api = 'api.huobi.pro';
     private $api = 'api.huobipro.com';
     public $api_method = '';
     public $req_method = '';
     protected $ACCOUNT_ID = '';
     protected $ACCESS_KEY = '';
     protected $SECRET_KEY = '';

     function __construct($accountId = '', $accessKey = '', $secretKey = '') {
          date_default_timezone_set("UTC");
          $this->ACCOUNT_ID = $accountId;
          $this->ACCESS_KEY = $accessKey;
          $this->SECRET_KEY = $secretKey;
     }
     /**
      * 行情类API
      */
     // 获取K线数据
     function get_history_kline($symbol = '', $period='',$size=0) {
          $this->api_method = "/market/history/kline";
          $this->req_method = 'GET';
          $param = [
               'symbol' => $symbol,
               'period' => $period
          ];
          if ($size) $param['size'] = $size;
          $url = $this->create_sign_url($param);
          return json_decode($this->curl($url));
     }
     // 获取聚合行情(Ticker)
     function get_detail_merged($symbol = '') {
          $this->api_method = "/market/detail/merged";
          $this->req_method = 'GET';
          $param = [
               'symbol' => $symbol,
          ];
          $url = $this->create_sign_url($param);
          return json_decode($this->curl($url));
     }
     // 获取 Market Depth 数据
     function get_market_depth($symbol = '', $type = '') {
          $this->api_method = "/market/depth";
          $this->req_method = 'GET';
          $param = [
               'symbol' => $symbol,
               'type' => $type
          ];
          $url = $this->create_sign_url($param);
          return json_decode($this->curl($url));
     }
     // 获取 Trade Detail 数据
     function get_market_trade($symbol = '') {
          $this->api_method = "/market/trade";
          $this->req_method = 'GET';
          $param = [
               'symbol' => $symbol
          ];
          $url = $this->create_sign_url($param);
          return json_decode($this->curl($url));
     }
     // 批量获取最近的交易记录
     function get_history_trade($symbol = '', $size = '') {
          $this->api_method = "/market/history/trade";
          $this->req_method = 'GET';
          $param = [
               'symbol' => $symbol
          ];
          if ($size) $param['size'] = $size;
          $url = $this->create_sign_url($param);
          return json_decode($this->curl($url));
     }
     // 获取 Market Detail 24小时成交量数据
     function get_market_detail($symbol = '') {
          $this->api_method = "/market/detail";
          $this->req_method = 'GET';
          $param = [
               'symbol' => $symbol
          ];
          $url = $this->create_sign_url($param);
          return json_decode($this->curl($url));
     }
     /**
      * 公共类API
      */
     // 查询系统支持的所有交易对及精度
     function get_common_symbols() {
          $this->api_method = '/v1/common/symbols';
          $this->req_method = 'GET';
          $url = $this->create_sign_url([]);
          return json_decode($this->curl($url));
     }
     // 查询系统支持的所有币种
     function get_common_currencys() {
          $this->api_method = "/v1/common/currencys";
          $this->req_method = 'GET';
          $url = $this->create_sign_url([]);
          return json_decode($this->curl($url));
     }
     // 查询系统当前时间
     function get_common_timestamp() {
          $this->api_method = "/v1/common/timestamp";
          $this->req_method = 'GET';
          $url = $this->create_sign_url([]);
          return json_decode($this->curl($url));
     }
     // 查询当前用户的所有账户(即account-id)
     function get_account_accounts() {
          $this->api_method = "/v1/account/accounts";
          $this->req_method = 'GET';
          $url = $this->create_sign_url([]);
          return json_decode($this->curl($url));
     }
     // 查询指定账户的余额
     function get_account_balance() {
          $this->api_method = "/v1/account/accounts/".$this->ACCOUNT_ID."/balance";
          $this->req_method = 'GET';
          $url = $this->create_sign_url([]);
          return json_decode($this->curl($url));
     }
     /**
      * 交易类API
      */
     // 下单
     function place_order($account_id=0,$amount=0,$price=0,$symbol='',$type='') {
          $source = 'api';
          $this->api_method = "/v1/order/orders/place";
          $this->req_method = 'POST';
          // 数据参数
          $postdata = [
               'account-id' => $account_id,
               'amount' => $amount,
               'source' => $source,
               'symbol' => $symbol,
               'type' => $type
          ];
          if ($price) {
               $postdata['price'] = $price;
          }
          $url = $this->create_sign_url();
          $return = $this->curl($url,$postdata);
          return json_decode($return);
     }
     // 申请撤销一个订单请求
     function cancel_order($order_id) {
          $source = 'api';
          $this->api_method = '/v1/order/orders/'.$order_id.'/submitcancel';
          $this->req_method = 'POST';
          $postdata = [];
          $url = $this->create_sign_url();
          $return = $this->curl($url,$postdata);
          return json_decode($return);
     }
     // 批量撤销订单
     function cancel_orders($order_ids = []) {
          $source = 'api';
          $this->api_method = '/v1/order/orders/batchcancel';
          $this->req_method = 'POST';
          $postdata = [
               'order-ids' => $order_ids
          ];
          $url = $this->create_sign_url();
          $return = $this->curl($url,$postdata);
          return json_decode($return);
     }
     // 查询某个订单详情
     function get_order($order_id) {
          $this->api_method = '/v1/order/orders/'.$order_id;
          $this->req_method = 'GET';
          $url = $this->create_sign_url();
          $return = $this->curl($url);
          return json_decode($return);
     }
     // 查询某个订单的成交明细
     function get_order_matchresults($order_id = 0) {
          $this->api_method = '/v1/order/orders/'.$order_id.'/matchresults';
          $this->req_method = 'GET';
          $url = $this->create_sign_url();
          $return = $this->curl($url);
//          $return = $this->curl($url,$postdata);
          return json_decode($return);
     }
     // 查询当前委托、历史委托
     function get_order_orders($symbol = '', $types = '',$start_date = '',$end_date = '',$states = '',$from = '',$direct='',$size = '') {
          $this->api_method = '/v1/order/orders';
          $this->req_method = 'GET';
          $postdata = [
               'symbol' => $symbol,
               'states' => $states
          ];
          if ($types) $postdata['types'] = $types;
          if ($start_date) $postdata['start-date'] = $start_date;
          if ($end_date) $postdata['end-date'] = $end_date;
          if ($from) $postdata['from'] = $from;
          if ($direct) $postdata['direct'] = $direct;
          if ($size) $postdata['size'] = $size;
          $url = $this->create_sign_url();
          $return = $this->curl($url,$postdata);
          return json_decode($return);
     }
     // 查询当前成交、历史成交
     function get_orders_matchresults($symbol = '', $types = '',$start_date = '',$end_date = '',$from = '',$direct='',$size = '') {
          $this->api_method = '/v1/order/matchresults';
          $this->req_method = 'GET';
          $postdata = [
               'symbol' => $symbol
          ];
          if ($types) $postdata['types'] = $types;
          if ($start_date) $postdata['start-date'] = $start_date;
          if ($end_date) $postdata['end-date'] = $end_date;
          if ($from) $postdata['from'] = $from;
          if ($direct) $postdata['direct'] = $direct;
          if ($size) $postdata['size'] = $size;
          $url = $this->create_sign_url();
          $return = $this->curl($url,$postdata);
          return json_decode($return);
     }
     // 获取账户余额
     function get_balance() {
          $account_id=$this->ACCOUNT_ID;
          $this->api_method = "/v1/account/accounts/{$account_id}/balance";
          $this->req_method = 'GET';
          $url = $this->create_sign_url();
          $return = $this->curl($url);
          $result = json_decode($return);
          return $result;
     }
     /**
      * 借贷类API
      */
     // 现货账户划入至借贷账户
     function dw_transfer_in($symbol = '',$currency='',$amount='') {
          $this->api_method = "/v1/dw/transfer-in/margin";
          $this->req_method = 'POST';
          $postdata = [
               'symbol	' => $symbol,
               'currency' => $currency,
               'amount' => $amount
          ];
          $url = $this->create_sign_url($postdata);
          $return = $this->curl($url);
          $result = json_decode($return);
          return $result;
     }
     // 借贷账户划出至现货账户
     function dw_transfer_out($symbol = '',$currency='',$amount='') {
          $this->api_method = "/v1/dw/transfer-out/margin";
          $this->req_method = 'POST';
          $postdata = [
               'symbol	' => $symbol,
               'currency' => $currency,
               'amount' => $amount
          ];
          $url = $this->create_sign_url($postdata);
          $return = $this->curl($url);
          $result = json_decode($return);
          return $result;
     }
     // 申请借贷
     function margin_orders($symbol = '',$currency='',$amount='') {
          $this->api_method = "/v1/margin/orders";
          $this->req_method = 'POST';
          $postdata = [
               'symbol	' => $symbol,
               'currency' => $currency,
               'amount' => $amount
          ];
          $url = $this->create_sign_url($postdata);
          $return = $this->curl($url);
          $result = json_decode($return);
          return $result;
     }
     // 归还借贷
     function repay_margin_orders($order_id='',$amount='') {
          $this->api_method = "/v1/margin/orders/{$order_id}/repay";
          $this->req_method = 'POST';
          $postdata = [
               'amount' => $amount
          ];
          $url = $this->create_sign_url($postdata);
          $return = $this->curl($url);
          $result = json_decode($return);
          return $result;
     }
     // 借贷订单
     function get_loan_orders($symbol='',$currency='',$start_date,$end_date,$states,$from,$direct,$size) {
          $this->api_method = "/v1/margin/loan-orders";
          $this->req_method = 'GET';
          $postdata = [
               'symbol' => $symbol,
               'currency' => $currency,
               'states' => $states
          ];
          if ($currency) $postdata['currency'] = $currency;
          if ($start_date) $postdata['start-date'] = $start_date;
          if ($end_date) $postdata['end-date'] = $end_date;
          if ($from) $postdata['from'] = $from;
          if ($direct) $postdata['direct'] = $direct;
          if ($size) $postdata['size'] = $size;
          $url = $this->create_sign_url($postdata);
          $return = $this->curl($url);
          $result = json_decode($return);
          return $result;
     }
     // 借贷账户详情
     function margin_balance($symbol='') {
          $this->api_method = "/v1/margin/accounts/balance";
          $this->req_method = 'POST';
          $postdata = [
          ];
          if ($symbol) $postdata['symbol'] = $symbol;
          $url = $this->create_sign_url($postdata);
          $return = $this->curl($url);
          $result = json_decode($return);
          return $result;
     }
     /**
      * 虚拟币提现API
      */
     // 申请提现虚拟币
     function withdraw_create($address='',$amount='',$currency='',$fee='',$addr_tag='') {
          $this->api_method = "/v1/dw/withdraw/api/create";
          $this->req_method = 'POST';
          $postdata = [
               'address' => $address,
               'amount' => $amount,
               'currency' => $currency
          ];
          if ($fee) $postdata['fee'] = $fee;
          if ($addr_tag) $postdata['addr-tag'] = $addr_tag;
          $url = $this->create_sign_url($postdata);
          $return = $this->curl($url);
          $result = json_decode($return);
          return $result;
     }
     // 申请取消提现虚拟币
     function withdraw_cancel($withdraw_id='') {
          $this->api_method = "/v1/dw/withdraw-virtual/{$withdraw_id}/cancel";
          $this->req_method = 'POST';
          $url = $this->create_sign_url();
          $return = $this->curl($url);
          $result = json_decode($return);
          return $result;
     }
     /**
      * 类库方法
      */
     // 生成验签URL
     function create_sign_url($append_param = []) {
          // 验签参数
          $param = [
               'AccessKeyId' => $this->ACCESS_KEY,
               'SignatureMethod' => 'HmacSHA256',
               'SignatureVersion' => 2,
               'Timestamp' => date('Y-m-d\TH:i:s', time())
          ];
          if ($append_param) {
               foreach($append_param as $k=>$ap) {
                    $param[$k] = $ap;
               }
          }
          return 'https://'.$this->api.$this->api_method.'?'.$this->bind_param($param);
     }
     // 组合参数
     function bind_param($param) {
          $u = [];
          $sort_rank = [];
          foreach($param as $k=>$v) {
               $u[] = $k."=".urlencode($v);
               $sort_rank[] = ord($k);
          }
          asort($u);
          $u[] = "Signature=".urlencode($this->create_sig($u));
          return implode('&', $u);
     }
     // 生成签名
     function create_sig($param) {
          $sign_param_1 = $this->req_method."\n".$this->api."\n".$this->api_method."\n".implode('&', $param);
          $signature = hash_hmac('sha256', $sign_param_1, $this->SECRET_KEY, true);
          return base64_encode($signature);
     }
     function curl($url,$postdata=[]) {
          $ch = curl_init();
          curl_setopt($ch,CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36');
          if ($this->req_method == 'POST') {
               curl_setopt($ch, CURLOPT_POST, 1);
               curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
          }
          curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
          curl_setopt($ch,CURLOPT_HEADER,0);
          curl_setopt($ch, CURLOPT_TIMEOUT,60);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
          curl_setopt ($ch, CURLOPT_HTTPHEADER, [
               "Content-Type: application/json",
          ]);
          $output = curl_exec($ch);
          $info = curl_getinfo($ch);
          curl_close($ch);
          return $output;
     }

     public static function getDepthAnalysis($ticker)
     {
          $huoBi = app('HuoBi');
          $depths = $huoBi->get_market_depth($ticker, 'step0');
          $bids = $depths->tick->bids;
          $asks = $depths->tick->asks;
          $buyCount = intval(count($bids)/2);
          $askCount = intval(count($asks)/2);
          $buyPartHigh = 0;
          $buyPartLow = 0;
          foreach ($bids as $k => $b) {
               if ($k < $buyCount) {
                    $buyPartHigh += $bids[$k][1];
               } else {
                    $buyPartLow += $bids[$k][1];
               }
          }
          $askPartHigh = 0;
          $askPartLow = 0;
          foreach ($asks as $k => $a) {
               if ($k < $askCount) {
                    $askPartLow += $asks[$k][1];
               } else {
                    $askPartHigh += $asks[$k][1];
               }
          }
          $buyAna = $buyPartHigh / ($buyPartHigh + $buyPartLow);
          $askAna = $askPartLow / ($askPartLow + $askPartHigh);
          if (($buyPartHigh + $buyPartLow) >= ($askPartHigh - $askPartLow)) {
               $buyDelAsk = ($buyPartHigh + $buyPartLow - $askPartHigh - $askPartLow) / ($askPartHigh + $askPartLow);
          } else {
               $buyDelAsk = - ($askPartHigh + $askPartLow - $buyPartHigh - $buyPartLow) / ($buyPartHigh + $buyPartLow);
          }
          return [
               'ticker' => $ticker,
               'buy' => $buyAna,
               'ask' => $askAna,
               'del' => $buyDelAsk
          ];
     }

     public static function putAllDepth()
     {
          $huoBi = app('HuoBi');
          $depths = $huoBi->get_common_symbols();
          $data = $depths->data;
          $tickers = [];
          $analysis = [];
          foreach ($data as $d) {
               $d = (array)$d;
               if ($d['quote-currency'] == 'eth') continue;
               array_push($tickers, $d['base-currency'].$d['quote-currency']);
          }
          foreach ($tickers as $ticker) {
               $analysis[] = self::getDepthAnalysis($ticker);
          }
          Redis::set('huobi_all_depth', json_encode($analysis));
     }
}
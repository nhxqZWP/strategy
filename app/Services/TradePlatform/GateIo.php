<?php

namespace App\Services\TradePlatform;

use Exception;

/**
 * Class GateIo
 * @package App\Services\TradePlatform
 *
 * public API methods
 * 所有交易对 get_pairs()
 * 交易市场订单参数 get_marketinfo()
 * 交易市场详细行情 get_marketlist()
 * 所有交易行情 get_tickers()
 * 单项交易行情 get_ticker('eth_btc')
 * 交易对的市场深度 get_orderbooks()
 * 指定交易对的市场深度 get_orderbook('btc_usdt') (委托挂单)
 * 历史成交记录 get_trade_history('btc_usdt', 1000)
 *
 * private API methods
 * 获取账号资金余额 get_balances()
 * 获取充值地址 deposit_address('btc')
 * 获取充值提现历史 deposites_withdrawals('1469092370', '1670713981')
 * 下单交易买入 buy('etc_btc', '0.0035', '0.3')
 * 下单交易卖出 sell('etc_btc', '0.00214', '0.3')
 * 取消下单 cancel_order(263393711)
 * 取消所有下单 cancel_all_orders('0', 'etc_btc')
 * 获取下单状态 get_order(263393711)
 * 获取我的当前挂单列表 open_orders()
 * 获取我的24小时内成交记录 get_trade_history('eth_btc',27817390)
 * 提现 withdraw('btc','11','your wallet address')
 */

class GateIo
{
    static $ch1 = null;
    static $ch2 = null;

    private static function gate_query($path, array $req = array())
    {
        // API settings, add your Key and Secret at here
        $key = config('platform.gate_io.key');
        $secret = config('platform.gate_io.secret');
        // generate a nonce as microtime, with as-string handling to avoid problems with 32bits systems
        $mt = explode(' ', microtime());
        $req['nonce'] = $mt[1] . substr($mt[0], 2, 6);
        // generate the POST data string
        $post_data = http_build_query($req, '', '&');
        $sign = hash_hmac('sha512', $post_data, $secret);
        // generate the extra headers
        $headers = array(
            'KEY: ' . $key,
            'SIGN: ' . $sign,
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8'
        );
        $ch = self::$ch1;
        if (is_null($ch)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36');
        }
        curl_setopt($ch, CURLOPT_URL, 'http://data.gate.io/api2/' . $path);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        // run the query
        $res = curl_exec($ch);
//        $getinfo = curl_getinfo($ch);
        if ($res === false) throw new Exception('Could not get reply: ' . curl_error($ch));
        $dec = json_decode($res, true);
        if (!$dec) throw new Exception('Invalid data received, please make sure connection is working and requested API exists: ' . $res);
        return $dec;
    }

    private static function curl_file_get_contents($url)
    {
        // our curl handle (initialize if required)
        $ch = self::$ch2;
        if (is_null($ch)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT,
                'Mozilla/4.0 (compatible; gate PHP bot; ' . php_uname('a') . '; PHP/' . phpversion() . ')'
            );
        }
        curl_setopt($ch, CURLOPT_URL, 'https://data.gate.io/api2/' . $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // run the query
        $res = curl_exec($ch);
        if ($res === false) throw new Exception('Could not get reply: ' . curl_error($ch));
        $dec = json_decode($res, true);
        if (!$dec) throw new Exception('Invalid data: ' . $res);

        return $dec;
    }

    public static function get_top_rate($currency_pair, $type = 'BUY')
    {
        $url = '1/orderBook/' . strtoupper($currency_pair);
        $json = self::curl_file_get_contents($url);

        $rate = 0;
        if (strtoupper($type) == 'BUY') {
            $r = $json['bids'][0];
            $rate = $r[0];
        } else {
            $r = end($json['asks']);
            $rate = $r[0];
        }
        return $rate;
    }

    public static function get_pairs()
    {
        $url = '1/pairs';
        $json = self::curl_file_get_contents($url);

        return $json;
    }

    public static function get_marketinfo()
    {

        $url = '1/marketinfo';
        $json = self::curl_file_get_contents($url);

        return $json;
    }

    public static function get_marketlist()
    {
        $url = '1/marketlist';
        $json = self::curl_file_get_contents($url);
        return $json;
    }

    public static function get_tickers()
    {

        $url = '1/tickers';
        $json = self::curl_file_get_contents($url);

        return $json;
    }

    public static function get_ticker($current_pairs)
    {

        $url = '1/ticker/' . strtoupper($current_pairs);
        $json = self::curl_file_get_contents($url);

        return $json;
    }

    public static function get_orderbooks()
    {

        $url = '1/orderBooks';
        $json = self::curl_file_get_contents($url);

        return $json;
    }

    public static function get_orderbook($current_pairs)
    {

        $url = '1/orderBook/' . strtoupper($current_pairs);
        $json = self::curl_file_get_contents($url);

        return $json;
    }

    public static function get_trade_history($current_pairs, $tid)
    {

        $url = '1/tradeHistory/' . strtoupper($current_pairs) . '/' . $tid;
        $json = self::curl_file_get_contents($url);

        return $json;
    }

    public static function get_balances()
    {

        return self::gate_query('1/private/balances');
    }

    public static function get_order_trades($order_number)
    {

        return self::gate_query('1/private/orderTrades',
            array(
                'orderNumber' => $order_number
            )
        );
    }

    public static function withdraw($currency, $amount, $address)
    {

        return self::gate_query('1/private/withdraw',
            array(
                'currency' => strtoupper($currency),
                'amount' => $amount,
                'address' => $address
            )
        );
    }

    public static function get_order($order_number)
    {

        return self::gate_query('1/private/getOrder',
            array(
                'orderNumber' => $order_number
            )
        );
    }

    public static function cancel_order($order_number)
    {

        return self::gate_query('1/private/cancelOrder',
            array(
                'orderNumber' => $order_number
            )
        );
    }

    public static function cancel_orders($orders)
    {
        return self::gate_query('1/private/cancelOrders',
//           ['orders_json'=> $orders]
            ['orders_json' => json_encode($orders)]
        );
    }

    public static function cancel_all_orders($type, $currency_pair)
    {

        return self::gate_query('1/private/cancelAllOrders',
            array(
                'type' => $type,
                'currencyPair' => strtoupper($currency_pair)
            )
        );
    }

    public static function sell($currency_pair, $rate, $amount)
    {

        return self::gate_query('1/private/sell',
            array(
                'currencyPair' => strtoupper($currency_pair),
                'rate' => $rate,
                'amount' => $amount,
            )
        );
    }

    public static function buy($currency_pair, $rate, $amount)
    {

        return self::gate_query('1/private/buy',
            array(
                'currencyPair' => strtoupper($currency_pair),
                'rate' => $rate,
                'amount' => $amount,
            )
        );
    }

    public static function get_my_trade_history($currency_pair, $order_number)
    {

        return self::gate_query('1/private/tradeHistory',
            array(
                'currencyPair' => strtoupper($currency_pair),
                'orderNumber' => $order_number
            )
        );
    }

    public static function get_my_trade_history_all($currency_pair)
    {

        return self::gate_query('1/private/tradeHistory',
            array(
                'currencyPair' => strtoupper($currency_pair)
            )
        );
    }

    public static function open_orders($currency_pair = '')
    {

        return self::gate_query('1/private/openOrders',
            array(
                'currencyPair' => strtoupper($currency_pair)
            ));
    }

    public static function deposites_withdrawals($start, $end)
    {

        return self::gate_query('1/private/depositsWithdrawals',
            array(
                'start' => $start,
                'end' => $end
            )
        );
    }

    public static function new_adddress($currency)
    {

        return self::gate_query('1/private/newAddress',
            array(
                'currency' => strtoupper($currency)
            )
        );
    }

    public static function deposit_address($currency)
    {

        return self::gate_query('1/private/depositAddress',
            array(
                'currency' => strtoupper($currency)
            )
        );
    }

    public static function check_username($username, $phone, $sign)
    {


        return self::gate_query('1/checkUsername',
            array(
                'username' => $username,
                'phone' => $phone,
                'sign' => $sign
            )
        );
    }
}

<?php

namespace App\Services\TradePlatform;

use Exception;

class Binance
{
    private static function query_un_key($path)
    {
        $mt = explode(' ', microtime());
        $req['nonce'] = $mt[1] . substr($mt[0], 2, 3);  //毫秒
        $post_data = http_build_query($req, '', '&');

        $headers = array(
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8'
        );
        $ch = null;
        if (is_null($ch)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36');
        }
        curl_setopt($ch, CURLOPT_URL, 'https://api.binance.com' . $path);
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

    private function query_key($path)
    {
        $key = config('platform.binance.key');
        $secret = config('platform.binance.secret');

        $mt = explode(' ', microtime());
        $req['nonce'] = $mt[1] . substr($mt[0], 2, 3);  //毫秒
        $post_data = http_build_query($req, '', '&');
        $sign = hash_hmac('wincoin', $post_data, $secret);

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
        curl_setopt($ch, CURLOPT_URL, 'https://api.binance.com' . $path);
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

    public static function TestConnectivity()
    {
        $path = '/api/v1/ping';
        $res = self::query_un_key($path);
        dd($res);
    }

}
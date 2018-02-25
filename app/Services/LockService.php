<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class LockService
{
    const PREFIX = 'wincoinpro:lock:';

    public static function lock($key, $expire = 5, $retry = 3)
    {
        $isLock = false;
        try {
            for ($i = 1; $i <= $retry; $i++) {
                $res = Redis::executeRaw(['SET', self::PREFIX . $key, '1', 'EX', $expire, 'NX']);
                if ($res == 'OK') {
                    $isLock = true;
                    break;
                }
                if ($i < $retry) {
                    usleep(20000); // 20ms
                }
            }
        } catch (Exception $e) {
            Log::error('redis lock fail: ' . $e->getMessage(), __FILE__, __LINE__);
        }
        return $isLock;
    }

    public static function unlock($key)
    {
        return Redis::del(self::PREFIX . $key);
    }
}
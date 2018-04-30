<?php

return [

    'binance' => [
        'key' => env('BINANCE_KEY', ''),
        'secret' => env('BINANCE_SECRET', '')
    ],

    'gate_io' => [
        'key' => env('GATE_KEY', ''),
        'secret' => env('GATE_SECRET', '')
    ],
    'huobi' => [
         'accountId' => env('HUOBI_ACCOUNT_ID', ''),
         'accessKey' => env('HUOBI_ACCESS_KEY', ''),
         'secretKey' => env('HUOBI_SECRET_KEY', '')
    ]
];

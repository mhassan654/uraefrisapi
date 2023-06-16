<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'taxpayer'=>['DEVICE_NO' => env('DEVICE_NO'),
    'TIN' => env('TIN'),
    'IS_VAT_REGISTERED' => env('IS_VAT_REGISTERED', 101),
    'LEGAL_NAME' => env('LEGAL_NAME'),
    'BIZ_NAME' => env('BIZ_NAME'),
    'ADDRESS' => env('ADDRESS'),
    'MOBILE_PHONE' => env('MOBILE_PHONE'),
    'LINE_PHONE' => env('LINE_PHONE'),
    'EMAIL' => env('EMAIL'),
    'APP_ID' => env('APP_ID', 'AP04'),
    'INDUSTRY_CODE' => env('INDUSTRY_CODE', 101),
    'BRN' => env('BRN'),
    'DATA_EXCHANGE_ID' => env('DATA_EXCHANGE_ID', '1'),
    'DEVICE_MAC' => env('DEVICE_MAC', '1'),
    'LONGITUDE' => env('LONGITUDE', '0'),
    'LATITUDE' => env('LATITUDE', '0'),
    'REQUEST_CODE' => env('REQUEST_CODE', 'TP'),
    'RESPONSE_CODE' => env('RESPONSE_CODE', 'TA'),
    'TAXPAYER_ID' => env('TAXPAYER_ID'),
    'DATA_SOURCE' => env('DATA_SOURCE', 203),
    'USERNAME' => env('USERNAME', 'admin'),
    'VERSION' => env('VERSION', '1.1.20191201'),
    'KUMUSOFT_MIDDLEWARE_URL' => env('KUMUSOFT_MIDDLEWARE_URL', 'http://localhost:'.env('KAKASAPORT', 3000)),
    'OFFLINE_SERVER_URL' => env('OFFLINE_SERVER_URL', 'http://127.0.0.1:9880/efristcs/ws/tcsapp/getInformation'),
    'OFFLINE_SERVER_INFO' => env('OFFLINE_SERVER_INFO', 'http://127.0.0.1:9880/efristcs/ws/tcsapp/tcsInfo'),
    'DATE_FORMART' => env('DATE_FORMART', 'dd/MM/yyyy'),
    'TIME_FORMART' => env('TIME_FORMART', 'dd/MM/yyyy HH:mm:ss'),
    'creditNoteValuePercentLimit' => env('creditNoteValuePercentLimit', '0.6'),
    'creditNoteMaximumInvoicingDays' => env('creditNoteMaximumInvoicingDays', '100'),]
];

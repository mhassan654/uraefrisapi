<?php

namespace Mhassan654\Uraefrisapi\Services;

use Illuminate\Support\Facades\Http;

class DeviceInformation
{
    protected static $url;

    public function __construct($url)
    {
        self::$url = $url;
    }

    public static function info($deviceInfo)
    {
        $response = Http::get(self::$url);

        if ($response->failed()) {
            return $response->body();
        } else {
            return $response;
        }
    }
}

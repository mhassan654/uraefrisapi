<?php

namespace Mhassan654\Uraefrisapi\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mhassan654\Uraefrisapi\Exceptions\ErrorResponse;
use Mhassan654\Uraefrisapi\Models\ActivityLog;
use Mhassan654\Uraefrisapi\Models\KumusoftKakasa;

class LoggerMiddleware
{
    public function handle($request, Closure $next)
    {
        $requestTime = Carbon::now()->format('Y-m-d H:i:s');
        $ip = $request->headers->get('cf-connecting-ip') ?: $request->headers->get('x-forwarded-for') ?: $request->ip();
        $_requestedUrl = $request->url();

        //Logger Object
        $act = new ActivityLog([
            'api_endpoint' => $_requestedUrl,
            'ip_address' => $ip,
            'request_time' => $requestTime,
            'api_client' => 'postman',
        ]);

        //save activity log, to file
        logger()->info(json_encode($act));

        return $next($request);
    }

    /**
     * @throws ErrorResponse
     */
    public static function userActivityLog($request, $response)
    {
        $request_data = KumusoftKakasa::prepareRequestData('', 'T103');
        // dd($response);
        $ipAddress = $response->headers('cf-connecting-ip') ?: $response->headers('x-forwarded-for') ?: $request->ip();
        $_requestedUrl = $response->header('url');
        $_requestBody = $response->header('all');
        $_requestClient = $response->header('User-Agent');
        $_requestMethod = $response->header('method');
        $returnCode = $response->json('returnStateInfo.returnCode');
        $returnMessage = $response->json('returnStateInfo.returnMessage');

        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

        try {
            if (! $response->json('returnStateInfo.returnCode') === '00') {
                throw new ErrorResponse('UserDetails details Error: '.$response->json('returnStateInfo.returnMessage'), 404);
            }
        } catch (\Exception $e) {
            throw new ErrorResponse('Error: '.$e->getMessage(), 404);
        }

        // dd($response->body());

        $data = json_decode(KumusoftKakasa::base64Decode($response->json('data.content')));

        //Logger Object
        $logs = new ActivityLog([
            'endPoint' => $_requestedUrl,
            'requestMethod' => $_requestMethod,
            'requestBody' => $_requestBody,
            'ip' => $ipAddress,
            'TIN' => $data->taxpayer->tin,
            'deviceNumber' => $data->device->deviceNo,
            'client' => $_requestClient,
            'response' => [
                'returnCode' => $returnCode,
                'returnMessage' => $returnMessage,
            ],
        ]);

        //save activity log to file
        Log::info(json_encode($logs));

        // return $next($request, $response);
    }
}

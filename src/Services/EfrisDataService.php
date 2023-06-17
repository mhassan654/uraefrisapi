<?php

namespace Mhassan654\Uraefrisapi\Services;

use App\Models\EfrisProduct;
use App\Models\KakasaCreditNote;
use App\Models\UnspscCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mhassan654\Uraefrisapi\Exceptions\ErrorResponse;
use Mhassan654\Uraefrisapi\Http\Middleware\LoggerMiddleware;
use Mhassan654\Uraefrisapi\Models\KakasaInvoice;
use Mhassan654\Uraefrisapi\Models\KumusoftKakasa;

class EfrisDataService

{
    /**
     * Get Server Configuration Information
     *
     * @return JsonResponse
     * @throws ErrorResponse
     */
    public function getServerInfo(): JsonResponse
    {
        $response = Http::get(config('uraefrisapi.taxpayer.OFFLINE_SERVER_INFO'));

        if (! $response->ok() || ! $response->json('onlineStatus')) {
            throw new ErrorResponse('Server is offline', 404);
        }

        return response()->json([
            'success' => true,
            'data' => $response->json(),
        ]);
    }

    /**
     * All Exchange Rates
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ErrorResponse
     */
    public function T126(): JsonResponse
    {
        $requestData = KumusoftKakasa::prepareRequestData('', 'T126');

        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $requestData);

        // logging endpoint
        LoggerMiddleware::userActivityLog($requestData, $response);

        if (! $response->json('returnStateInfo.returnCode') === '00') {
            throw new ErrorResponse('All Exchange Rates Error: '.$response->json('returnStateInfo.returnMessage'), 200);
        }

        $decodedContent = KumusoftKakasa::base64Decode($response->json('data.content'), $response->json('data.dataDescription.zipCode'));

        Log::info($decodedContent);

        return response()->json([
            'status' => $response->json('returnStateInfo'),
            'data' => json_decode($decodedContent),
        ]);
    }

    /**
     * The current Server Time.
     * The EFD time is synchronized with the server time.
     * Interface Code: T101
     *
     * @param Request $request
     * @return JsonResponse     *
     * @throws ErrorResponse
     */
    public function T101(): JsonResponse
    {
        $request_data = KumusoftKakasa::prepareRequestData('', 'T101');

        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

        try {
            if (! $response->json('returnStateInfo.returnCode') == '00') {
                throw new ErrorResponse('Server Time Error: '.$response->json('returnStateInfo.returnMessage'), 200);
            }
        } catch (\Exception $e) {
            throw new ErrorResponse('Server Time Error: '.$e->getMessage(), 200);
        }

        try {
            // You can uncomment the following line if needed
            // $serverTime = json_decode(KumusoftKakasa::base64Decode($response->json('data.content'), $response->json('data.dataDescription.zipCode')));

            return response()->json([
                'status' => $response->json('returnStateInfo'),
                'data' => json_decode(KumusoftKakasa::base64Decode($response->json('data.content'), $response->json('data.dataDescription.zipCode'))),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => $response->json('returnStateInfo'),
                'data' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Details of a specified invoice
     *
     * @param $invoice
     * @return JsonResponse
     */
    public function T108($invoice): JsonResponse
    {
        $content = [
            'invoiceNo' => $invoice,
        ];
        $request_data = KumusoftKakasa::prepareRequestData($content, 'T108');

        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

        try {
            $decodedResponse = json_decode(KumusoftKakasa::base64Decode($response->json('data.content'), $response->json('data.dataDescription.zipCode')), true);
            // store()->put($decodedResponse['basicInformation']['invoiceNo'], $decodedResponse);
            ////console.log("Invoice saved to DB", store()->get($decodedResponse['basicInformation']['invoiceNo']))
            return response()->json([
                'status' => $response->json('returnStateInfo'),
                'data' => $decodedResponse,
            ]);
        } catch (\Exception $e) {
            Log::info($e);

            return response()->json([
                'status' => $response->json('returnStateInfo'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Taxpayer Device Registration Details
     * Interface Code: T103
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ErrorResponse
     */
    public function T103(): JsonResponse
    {
        $request_data = KumusoftKakasa::prepareRequestData('', 'T103');

        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

        try {
            if (! $response->json('returnStateInfo.returnCode') == '00') {
                throw new ErrorResponse('Registration details Error: '.$response->json('returnStateInfo.returnMessage'), 200);
            }
        } catch (\Exception $e) {
            throw new ErrorResponse('Registration details Error: '.$e->getMessage(), 200);
        }

        try {
            $decodedResponse = json_decode(KumusoftKakasa::base64Decode($response->json('data.content'), $response->json('data.dataDescription.zipCode')), true);
            Log::info('Registration details Response', $decodedResponse);
        } catch (\Exception $e) {
        }

        try {
            return response()->json([
                'status' => $response->json('returnStateInfo'),
                'data' => json_decode(KumusoftKakasa::base64Decode($response->json('data.content'), $response->json('data.dataDescription.zipCode')), true),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => $response->json('returnStateInfo'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get Taxpayer information by TIN, BRN or NIN
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ErrorResponse
     */
    public function T119(Request $request): JsonResponse
    {
        $content = [
            'tin' => $request->input('tin'),
            'ninBrn' => $request->input('ninBrn'),
        ];
        $request_data = KumusoftKakasa::prepareRequestData($content, 'T119');

        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

        try {
            if (! $response->json('returnStateInfo.returnCode') == '00') {
                throw new ErrorResponse('Get Taxpayer information by TIN, BRN or NIN: '.$response->json('returnStateInfo.returnMessage'), 200);
            }
        } catch (\Exception $e) {
            throw new ErrorResponse('Get Taxpayer information by TIN, BRN or NIN: '.$e->getMessage(), 200);
        }

        try {
            $decodedResponse = json_decode(KumusoftKakasa::base64Decode($response->json('data.content'), $response->json('data.dataDescription.zipCode')), true);
            Log::info('Get Taxpayer information by TIN, BRN or NIN Response', $decodedResponse);
        } catch (\Exception $e) {
        }

        try {
            return response()->json([
                'status' => $response->json('returnStateInfo'),
                'data' => json_decode(KumusoftKakasa::base64Decode($response->json('data.content'), $response->json('data.dataDescription.zipCode')), true),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => $response->json('returnStateInfo'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Exchange Rate for one currency
     * Interface Code T121
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ErrorResponse
     */
    public function T121(Request $request): JsonResponse
    {
        $content = [
            'currency' => $request->route('currency'),
        ];
        $request_data = KumusoftKakasa::prepareRequestData($content, 'T121');

        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

        try {
            if (! $response->json('returnStateInfo.returnCode') == '00') {
                throw new ErrorResponse('Exchange Rate for one currency: '.$response->json('returnStateInfo.returnMessage'), 200);
            }
        } catch (\Exception $e) {
            throw new ErrorResponse('Exchange Rate for one currency: '.$e->getMessage(), 200);
        }

        try {
            $decodedResponse = json_decode(KumusoftKakasa::base64Decode($response->json('data.content'), $response->json('data.dataDescription.zipCode')), true);
            Log::info('Exchange Rate for one currency Response', $decodedResponse);
        } catch (\Exception $e) {
        }

        try {
            return response()->json([
                'status' => $response->json('returnStateInfo'),
                'data' => json_decode(KumusoftKakasa::base64Decode($response->json('data.content'), $response->json('data.dataDescription.zipCode')), true),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => $response->json('returnStateInfo'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Query the stock quantity by goods ID
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ErrorResponse
     */
    public function T128(Request $request): JsonResponse
    {
        $content = [
            'id' => $request->input('id'),
        ];
        $request_data = KumusoftKakasa::prepareRequestData($content, 'T128');

        $response = Http::post(config('taxpayer.OFFLINE_SERVER_URL'), $request_data);

        try {
            if (! $response->json('returnStateInfo.returnCode') == '00') {
                throw new ErrorResponse('Query the stock quantity by goods ID: '.$response->json('returnStateInfo.returnMessage'), 200);
            }
        } catch (\Exception $e) {
            throw new ErrorResponse('Query the stock quantity by goods ID: '.$e->getMessage(), 200);
        }

        try {
            $decodedResponse = json_decode(KumusoftKakasa::base64Decode($response->json('data.content'), $response->json('data.dataDescription.zipCode')), true);
            Log::info('Query the stock quantity by goods ID Response', $decodedResponse);
        } catch (\Exception $e) {
        }

        try {
            return response()->json([
                'status' => $response->json('returnStateInfo'),
                'data' => json_decode(KumusoftKakasa::base64Decode($response->json('data.content'), $response->json('data.dataDescription.zipCode')), true),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => $response->json('returnStateInfo'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Goods/Services query by product code
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @throws ErrorResponse
     */
    public function T144(Request $request): JsonResponse
    {
        $content = [
            'goodsCode' => $request->input('goodsCode'),
        ];
        $request_data = KumusoftKakasa::prepareRequestData($content, 'T144');

        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

        try {
            if (! $response->json('returnStateInfo.returnCode') == '00') {
                throw new ErrorResponse('Goods/Services query by product code: '.$response->json('returnStateInfo.returnMessage'), 200);
            }
        } catch (\Exception $e) {
            throw new ErrorResponse('Goods/Services query by product code: '.$e->getMessage(), 200);
        }

        try {
            $decodedResponse = json_decode(KumusoftKakasa::base64Decode($response->json('data.content'), $response->json('data.dataDescription.zipCode')), true);
            Log::info('Goods/Services query by product code Response', $decodedResponse);
        } catch (\Exception $e) {
        }

        try {
            return response()->json([
                'status' => $response->json('returnStateInfo'),
                'data' => json_decode(KumusoftKakasa::base64Decode($response->json('data.content'), $response->json('data.dataDescription.zipCode')), true),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => $response->json('returnStateInfo'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * EFRIS Dictionary/Dropdowns
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ErrorResponse
     */
    public function T176(Request $request): JsonResponse
    {
        $request_data = KumusoftKakasa::prepareRequestData('', 'T176');

        $response = Http::post(config('taxpayer.OFFLINE_SERVER_URL'), $request_data);

        try {
            if (! $response->json('returnStateInfo.returnCode') == '00') {
                throw new ErrorResponse('EFRIS Dictionary/Dropdowns: '.$response->json('returnStateInfo.returnMessage'), 200);
            }
        } catch (\Exception $e) {
            throw new ErrorResponse('EFRIS Dictionary/Dropdowns: '.$e->getMessage(), 200);
        }

        try {
            $decodedResponse = json_decode(KumusoftKakasa::base64Decode($response->json('data.content'), $response->json('data.dataDescription.zipCode')), true);
            Log::info('EFRIS Dictionary/Dropdowns Response', $decodedResponse);
        } catch (\Exception $e) {
        }

        try {
            return response()->json([
                'status' => $response->json('returnStateInfo'),
                'data' => json_decode(KumusoftKakasa::base64Decode($response->json('data.content'), $response->json('data.dataDescription.zipCode')), true),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => $response->json('returnStateInfo'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * EFRIS Dictionary/Dropdowns
     *
     * @param Request $req
     * @return JsonResponse
     * @throws ErrorResponse
     */
    public function T115(Request $req): JsonResponse
    {
        $request_data = KumusoftKakasa::prepareRequestData('', 'T115');

        // Post Data
        $response = Http::withOptions([
            'verify' => false,
            'json' => true,
        ])->post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

        // Logging endpoint
        LoggerMiddleware::userActivityLog($req, $response, function ($req, $res) {
        });

        try {
            if (! $response->body['returnStateInfo']['returnCode'] === '00') {
                return response()->json(['error' => "EFRIS Dictionary/Dropdowns: {$response->body['returnStateInfo']['returnMessage']}"], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => "EFRIS Dictionary/Dropdowns: {$e->getMessage()}"], 200);
        }

        try {
            $decodedContent = KumusoftKakasa::base64Decode($response->body['data']['content'], $response->body['data']['dataDescription']['zipCode']);
            $content = json_decode($decodedContent, true);

            return response()->json([
                'status' => $response->body['returnStateInfo'],
                'data' => $content,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => $response->body['returnStateInfo'],
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Register a product or Service
     *
     * @param Request $req
     * @return JsonResponse
     * @throws ErrorResponse
     */
    public function T130(Request $req): JsonResponse
    {
        $product = KumusoftKakasa::prepareInventoryData($req->input('products'));
        Log::info($req->input());
        $request_data = KumusoftKakasa::prepareRequestData($product, 'T130');

        // Post Data
        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

        // Logging endpoint
        LoggerMiddleware::userActivityLog($req, $response, function ($req, $res) {
        });

        try {
            if (! $response->body['returnStateInfo']['returnCode'] == '00') {
                return response()->json(['error' => "Register a product or Service: {$response->body['returnStateInfo']['returnMessage']}"], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => "Register a product or Service: {$e->getMessage()}"], 200);
        }

        try {
            $decodedContent = KumusoftKakasa::base64Decode($response->body['data']['content'], $response->body['data']['dataDescription']['zipCode']);
            Log::info($decodedContent);

            $feedback = json_decode($decodedContent, true);

            // Synchronize local products DB
            Log::info('FeedBack', $feedback);

            if (count($feedback) === 0) {
                // If this was successful
                Http::post(config('uraefrisapi.taxpayer.KUMUSOFT_MIDDLEWARE_URL').'/sync-products');

                // Feedback
                return response()->json([
                    'status' => $response->body['returnStateInfo'],
                    'data' => 'Product/Service Successfully added',
                ]);
            } else {
                // Feedback
                return response()->json([
                    'status' => $response->body['returnStateInfo'],
                    'data' => $feedback[0]['returnMessage'],
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => $response->body['returnStateInfo'],
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Active Invoices {Those which can be issued credit/debit notes}
     * T107
     *
     * @param Request $req
     * @return Response
     * @throws ErrorResponse
     */
    public function T107(Request $req)
    {
        $content = [
            'invoiceNo' => $req->input('invoiceNo'),
            'deviceNo' => config('uraefrisapi.taxpayer.DEVICE_NO'),
            'buyerTin' => $req->input('buyerTin'),
            'buyerLegalName' => $req->input('buyerLegalName'),
            'invoiceType' => $req->input('invoiceType'),
            'startDate' => $req->input('startDate'),
            'endDate' => $req->input('endDate'),
            'pageNo' => $req->input('pageNo'),
            'pageSize' => $req->input('pageSize'),
        ];

        $request_data = KumusoftKakasa::prepareRequestData($content, 'T107');

        // Post Data
        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

        // Logging endpoint
        LoggerMiddleware::userActivityLog($req, $response, function ($req, $res) {
        });

        try {
            if (! $response->body['returnStateInfo']['returnCode'] == '00') {
                return response()->json(['error' => "Active Invoices {Those which can be issued credit/debit notes}: {$response->body['returnStateInfo']['returnMessage']}"], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => "Active Invoices {Those which can be issued credit/debit notes}: {$e->getMessage()}"], 200);
        }

        try {
            $decodedContent = KumusoftKakasa::base64Decode($response->body['data']['content'], $response->body['data']['dataDescription']['zipCode']);
            Log::info($decodedContent);

            $content = json_decode($decodedContent, true);

            return response()->json([
                'status' => $response->body['returnStateInfo'],
                'data' => $content,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => $response->body['returnStateInfo'],
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function T109Invoice(Request $req)
    {
        $data = $req->input();

        $EfrisInvoice = new Invoice($data);
        $product = $EfrisInvoice->prepareInvoiceDetails($data);
        $request_data = KumusoftKakasa::prepareRequestData($product, 'T109');

        // Post Data
        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

        Log::info($response);

        return response()->json([
            'status' => $response->body['returnStateInfo'],
            'requestData' => $request_data,
        ]);
    }

    /**
     * Create invoices or Receipts in Bulk
     */
    public function T109Bulk(Request $request)
    {
        $invoices = $request->input('invoices');
        $efris_invoices = [];

        foreach ($invoices as $invoice) {
            $EfrisInvoice = new KakasaInvoice($invoice);
            $product = KakasaInvoice::prepareInvoiceDetails($invoice);
            $efris_invoices[] = KumusoftKakasa::prepareRequestData($product, 'T109');
        }

        $fiscalised_invoices = [];

        foreach ($efris_invoices as $item) {
            if ($item['hasErrors'] === 1) {
                unset($item['hasErrors']);
                $fiscalised_invoices[] = [
                    'status' => [
                        'returnCode' => '404',
                        'returnMessage' => $product[0],
                    ],
                ];
            } else {
                $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), [
                    'url' => config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'),
                    'body' => $item,
                    'json' => true,
                ]);

                try {
                    $fiscalised_invoices[] = [
                        'status' => $response->body['returnStateInfo'],
                        'data' => KumusoftKakasa::base64Decode($response->body['data']['content'], $response->body['data']['dataDescription']['zipCode']),
                    ];
                } catch (\Exception $err) {
                    $fiscalised_invoices[] = [
                        'status' => $response->body['returnStateInfo'],
                        'data' => $err,
                    ];
                }
            }
        }

        if ($err) {
            return response('An error occurred', 500);
        } else {
            return response()->json($fiscalised_invoices);
        }
    }

    /**
     * Create an Invoice or Receipt
     * Invoices for VAT registered taxpayers, Receipts for non-VAT registered taxpayers
     */
    public function T109(Request $request): JsonResponse
    {
        $data = $request->input('data');

        $EfrisInvoice = new KakasaInvoice($data);
        $product = $EfrisInvoice::prepareInvoiceDetails($data);

        if ($product['hasErrors'] === 1) {
            unset($product['hasErrors']);

            return response()->json([
                'status' => [
                    'returnCode' => '404',
                    'returnMessage' => $product[0],
                ],
            ]);
        } else {
            $request_data = KumusoftKakasa::prepareRequestData($product, 'T109');

            $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

            LoggerMiddleware::userActivityLog($request, $response);

            try {
                $decodedContent = KumusoftKakasa::base64Decode($response->body['data']['content'], $response->body['data']['dataDescription']['zipCode']);
                $resp = json_decode($decodedContent, true);

                store::set($resp['basicInformation']['invoiceNo'], $resp);

                return response()->json([
                    'status' => $response->body['returnStateInfo'],
                    'data' => $resp,
                ]);
            } catch (\Exception $error) {
                return response()->json([
                    'status' => $response->body['returnStateInfo'],
                ]);
            }
        }
    }

    /**
     * Create an Invoice or Receipt Preview
     * Invoices for VAT registered taxpayers, Receipts for non-VAT registered taxpayers
     */
    public function T109Preview(Request $request): JsonResponse
    {
        $data = $request->input('data');

        $EfrisInvoice = new KakasaInvoice($data);
        $product = $EfrisInvoice->prepareInvoiceDetails($data);

        if ($product['hasErrors'] === 1) {
            unset($product['hasErrors']);

            return response()->json([
                'status' => [
                    'returnCode' => '404',
                    'returnMessage' => $product[0],
                ],
            ]);
        } else {
            $request_data = KumusoftKakasa::prepareRequestData($product, 'T109');
            $decodedContent = KumusoftKakasa::base64Decode($request_data['data']['content'], $request_data['data']['dataDescription']['zipCode']);
            $resp = json_decode($decodedContent, true);

            return response()->json([
                'status' => [
                    'returnCode' => '00',
                    'returnMessage' => 'SUCCESS',
                ],
                'data' => $resp,
            ]);
        }
    }

    /**
     * Increase Stock for a given Item
     *
     * @return string
     * @throws ErrorResponse
     */
    public function T131up(Request $request)
    {
        $stockItems = EfrisProduct::prepareT131ProductList($request->input('stockInItem'));

        if ($stockItems['errorCode']) {
            return response()->json([
                'status' => [
                    'returnCode' => '45',
                    'returnMessage' => 'Partial failure!',
                ],
                'data' => $stockItems['errorMessage'],
            ]);
        } else {
            $data = [
                'goodsStockIn' => [
                    'operationType' => '101', //Increase
                    'supplierTin' => $request->input('supplierTin'),
                    'supplierName' => $request->input('supplierName'),
                    'remarks' => $request->input('remarks'),
                    'stockInDate' => $request->input('stockInDate'),
                    'stockInType' => $request->input('stockInType'),
                    'productionBatchNo' => $request->input('productionBatchNo'),
                    'productionDate' => $request->input('productionDate'),
                    'branchId' => $request->input('branchId'),
                ],
                'goodsStockInItem' => $stockItems,
            ];

            $request_data = KumusoftKakasa::prepareIncreaseRequestData($data, 'T131');

            $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'),$request_data);

            LoggerMiddleware::userActivityLog($request, $response);

            try {
                $decodedContent = KumusoftKakasa::base64Decode($response->body['data']['content'], $response->body['data']['dataDescription']['zipCode']);
                $resp = json_decode($decodedContent, true);

                if (count($resp) <= 0) {
                    return response()->json([
                        'status' => $response->body['returnStateInfo'],
                        'data' => 'Product Increased Successfully',
                    ]);
                }

                return response()->json([
                    'status' => $response->body['returnStateInfo'],
                    'data' => $resp[0]['returnMessage'],
                ]);
            } catch (\Exception $error) {
                return response()->json([
                    'status' => $response->body['returnStateInfo'],
                    'data' => $response->body['returnStateInfo']['returnMessage'],
                ]);
            }
        }
    }

    /**
     * Decrease stock of a given item
     */
    public function T131down(Request $request): JsonResponse
    {
        $stockItems = EfrisProduct::prepareT131ProductList($request->input('stockInItem'));

        if ($stockItems['errorCode']) {
            return response()->json([
                'status' => [
                    'returnCode' => '45',
                    'returnMessage' => 'Partial failure!',
                ],
                'data' => $stockItems['errorMessage'],
            ]);
        } else {
            $data = [
                'goodsStockIn' => [
                    'operationType' => '102', //Decrease
                    'adjustType' => $request->input('adjustType'),
                    'remarks' => $request->input('remarks'),
                ],
                'goodsStockInItem' => $stockItems,
            ];

            $request_data = KumusoftKakasa::prepareIncreaseRequestData($data, 'T131');

            $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

            LoggerMiddleware::userActivityLog($request, $response);

            try {
                $decodedContent = KumusoftKakasa::base64Decode($response->body['data']['content'], $response->body['data']['dataDescription']['zipCode']);
                $resp = json_decode($decodedContent, true);

                if (count($resp) <= 0) {
                    return response()->json([
                        'status' => $response->body['returnStateInfo'],
                        'data' => 'Product(s) Decreased Successfully',
                    ]);
                }

                return response()->json([
                    'status' => $response->body['returnStateInfo'],
                    'data' => $resp[0]['returnMessage'],
                ]);
            } catch (\Exception $error) {
                return response()->json([
                    'status' => $response->body['returnStateInfo'],
                    'data' => $response->body['returnStateInfo']['returnMessage'],
                ]);
            }
        }
    }

    /**
     * Pick the UNSPSC
     *
     * @return JsonResponse
     * @throws ErrorResponse
     */
    public function T124(Request $request)
    {
        $data = [
            'pageNo' => $request->input('pageNo'),
            'pageSize' => $request->input('pageSize'),
        ];

        $request_data = KumusoftKakasa::prepareRequestData($data, 'T124');

        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

        // Logging endpoint
        LoggerMiddleware::userActivityLog($request, $response);

        try {
            if (! $response->body['returnStateInfo']['returnCode'] === '00') {
                return response()->json([
                    'message' => 'Pick the UNSPSC: '.$response->body['returnStateInfo']['returnMessage'],
                ], 200);
            }

            Log::info(KumusoftKakasa::base64Decode($response->body['data']));
        } catch (\Exception $error) {
            return response()->json([
                'message' => 'Pick the UNSPSC: '.$error->getMessage(),
            ], 200);
        }
    }

    /**
     * Pick UNSPSC codes
     *
     * @return JsonResponse
     */
    public function T124codes(): JsonResponse
    {
        $results = UnspscCode::all();

        if ($results->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'data' => $results,
            ]);
        } else {
            return response()->json([
                'status' => 'success',
                'data' => $results,
            ]);
        }
    }

    /**
     * UNSPSC code pagination
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function T124Unspsc(Request $request): JsonResponse
    {
        try {
            $page = $request->query('page', 1);
            $pagination = $request->query('pagination', 300);

            $startIndex = ($page - 1) * $pagination;
            $endIndex = $page * $pagination;

            $unspscs = UnspscCode::skip($startIndex)->take($pagination)->get();

            $count = UnspscCode::count();

            $next = ($endIndex < $count) ? $page + 1 : null;
            $previous = ($startIndex > 0) ? $page - 1 : null;

            if ($pagination > 999) {
                $pagination = 100;
            }

            return response()->json([
                'status' => 'success',
                'next' => $next,
                'previous' => $previous,
                'pageSize' => $pagination,
                'data' => $unspscs,
            ]);
        } catch (\Exception $error) {
            return response()->json([
                'status' => 'success',
                'error' => $error->getMessage(),
            ]);
        }
    }

    /**
     * Inquire info about Excercise Duty
     *
     * @return JsonResponse
     * @throws ErrorResponse
     */
    public function T125(): JsonResponse
    {
        $request_data = KumusoftKakasa::prepareRequestData('', 'T125');

        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

        // Logging endpoint
        LoggerMiddleware::userActivityLog($request_data, $response);

        try {
            if (! $response->body['returnStateInfo']['returnCode'] === '00') {
                return response()->json([
                    'message' => 'Inquire info about Excercise Duty: '.$response->body['returnStateInfo']['returnMessage'],
                ], 200);
            }
        } catch (\Exception $error) {
            return response()->json([
                'message' => 'Inquire info about Excercise Duty: '.$error->getMessage(),
            ], 200);
        }

        try {
            $content = KumusoftKakasa::base64Decode(
                $response->body['data']['content'],
                $response->body['data']['dataDescription']['zipCode']
            );

            $data = json_decode($content);

            return response()->json([
                'status' => $response->body['returnStateInfo'],
                'data' => $data,
            ]);
        } catch (\Exception $error) {
            return response()->json([
                'status' => $response->body['returnStateInfo'],
                'error' => $error->getMessage(),
            ]);
        }
    }

    /**
     * Get stock details
     *
     * @return JsonResponse
     * @throws ErrorResponse
     */
    public function T127(Request $request): JsonResponse
    {
        $data = [
            'goodsCode' => $request->input('goodsCode'),
            'goodsName' => $request->input('goodsName'),
            'commodityCategoryName' => $request->input('commodityCategoryName'),
            'pageNo' => $request->input('pageNo'),
            'pageSize' => $request->input('pageSize'),
        ];

        $request_data = KumusoftKakasa::prepareRequestData($data, 'T127');

        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

        // Logging endpoint
        LoggerMiddleware::userActivityLog($request, $response);

        try {
            $content = KumusoftKakasa::base64Decode(
                $response->body['data']['content'],
                $response->body['data']['dataDescription']['zipCode']
            );

            $responseData = json_decode($content);

            return response()->json([
                'status' => $response->body['returnStateInfo'],
                'data' => $responseData,
            ]);
        } catch (\Exception $error) {
            return response()->json([
                'status' => $response->body['returnStateInfo'],
                'error' => $error->getMessage(),
            ]);
        }
    }

    /**
     * Invoice Inquiry
     * Query all invoice information(Invoice /receipt CreditNode ,Debit Node,Cancel CreditNode ,Debit Node)
     *
     * @return JsonResponse
     * @throws ErrorResponse
     */
    public function T106(Request $request): JsonResponse
    {
        $data = $request->only([
            'oriInvoiceNo',
            'invoiceNo',
            'buyerTin',
            'buyerNinBrn',
            'buyerLegalName',
            'combineKeywords',
            'invoiceType',
            'invoiceKind',
            'isInvalid',
            'isRefund',
            'startDate',
            'endDate',
            'pageNo',
            'pageSize',
            'referenceNo',
        ]);

        // Prepare request data
        $request_data = KumusoftKakasa::prepareRequestData($data, 'T106');

        // Post Data
        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

        // Logging endpoint
        LoggerMiddleware::userActivityLog($request, $response);

        try {
            if (! $response->body()['returnStateInfo']['returnCode'] === '00') {
                return response()->json([
                    'status' => $response->body()['returnStateInfo'],
                    'error' => 'Invoice Inquiry: '.$response->body()['returnStateInfo']['returnMessage'],
                ], 200);
            }
        } catch (\Exception $error) {
            return response()->json([
                'status' => $response->body()['returnStateInfo'],
                'error' => 'Invoice Inquiry: '.$error->getMessage(),
            ], 200);
        }

        try {
            $decodedContent = KumusoftKakasa::base64Decode(
                $response->body()['data']['content'],
                $response->body()['data']['dataDescription']['zipCode']
            );

            $parsedContent = json_decode($decodedContent, true);

            return response()->json([
                'status' => $response->body()['returnStateInfo'],
                'data' => $parsedContent,
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'status' => $response->body()['returnStateInfo'],
                'error' => $error->getMessage(),
            ], 200);
        }
    }

    /**
     * Generate a credit note application
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function T110(Request $request): JsonResponse
    {
        $postedItems = $request->all();

        // 1. Get Invoice Details
        $invoice = $postedItems['generalInfo']['oriInvoiceNo'];

        // Pick the original invoice from URA
        $original_invoice = function () use ($invoice) {
            return Http::get(config('uraefrisapi.taxpayer.KUMUSOFT_MIDDLEWARE_URL').'/invoice-details/'.$invoice)->json()['data'];
        };

        // The Original invoice
        $original_inv = $original_invoice();

        // Credit note info
        $creditNote = new KakasaCreditNote(
            $postedItems['itemsBought'],
            $postedItems['generalInfo'],
            $original_inv
        );

        // Post Data
        $note = $creditNote->buildCreditNote();

        $request_data = KumusoftKakasa::prepareRequestData($note, 'T110');

        $response = Http::post(taxpayer::OFFLINE_SERVER_URL, $request_data);

        try {
            $decodedContent = KumusoftKakasa::base64Decode(
                $response->body()['data']['content'],
                $response->body()['data']['dataDescription']['zipCode']
            );

            $parsedContent = json_decode($decodedContent, true);

            return response()->json([
                'status' => $response->body()['returnStateInfo'],
                'data' => $parsedContent,
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'status' => $response->body()['returnStateInfo'],
                'error' => $error->getMessage(),
            ], 200);
        }
    }

    /**
     * Record stock for manufacturers
     *
     * @return JsonResponse
     * @throws ErrorResponse
     */
    public function manufacturerStockIn(Request $request): JsonResponse
    {
        $manufacturing = $request->input('data');

        // 1. List of raw materials data be reduced from stock
        $reduce_rawmaterials = [
            'goodsStockIn' => [
                'operationType' => '102', // Decrease
                'adjustType' => '104', // Raw materials
                'remarks' => $manufacturing['remarks'],
            ],
            'goodsStockInItem' => EfrisProduct::prepareT131ProductList($manufacturing['rawMaterials']),
        ];

        // 2. Request data for the raw materials
        $rawmaterials_requestdata = KumusoftKakasa::prepareRequestData($reduce_rawmaterials, 'T131');

        // 3. List of finished products
        $increase_finishedproducts = [
            'goodsStockIn' => [
                'operationType' => '101', // Increase
                'remarks' => $manufacturing['remarks'],
                'stockInDate' => $manufacturing['stockInDate'],
                'stockInType' => '103', // Manufacturing
                'productionBatchNo' => $manufacturing['productionBatchNo'],
                'productionDate' => $manufacturing['productionDate'],
            ],
            'goodsStockInItem' => EfrisProduct::prepareT131ProductList($manufacturing['finalProducts']),
        ];

        // 4. Request data for the finished products
        $finishedproducts_requestdata = KumusoftKakasa::prepareRequestData($increase_finishedproducts, 'T131');

        // 5. Send Reduction request to server
        Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $rawmaterials_requestdata);

        // 6. Send Increment request to server
        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $finishedproducts_requestdata);

        LoggerMiddleware::userActivityLog($request, $response);

        try {
            if ($response->body()['returnStateInfo']['returnCode'] !== '00') {
                throw new errorResponse("Record stock for manufacturers: {$response->body()['returnStateInfo']['returnMessage']}", 200);
            }
        } catch (\Exception $error) {
            throw new ErrorResponse("Record stock for manufacturers: {$error->getMessage()}", 200);
        }

        try {
            $decodedContent = KumusoftKakasa.base64Decode($response->body()['data']['content']);
            $parsedContent = json_decode($decodedContent, true);

            return response()->json([
                'status' => $response->body()['returnStateInfo'],
                'data' => $parsedContent,
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'status' => $response->body()['returnStateInfo'],
                'error' => $error->getMessage(),
            ], 200);
        }
    }

    /**
     * Synchronize URA product list with the local list
     *
     * @return JsonResponse
     * @throws ErrorResponse
     */
    public function synchProductsDatabase(Request $request): JsonResponse
    {
        // Request Data
        $request_data = KumusoftKakasa::prepareRequestData([], 'T127');

        // Post Data
        $response = Http::post(taxpayer::OFFLINE_SERVER_URL, $request_data);

        // Logging endpoint
        LoggerMiddleware::userActivityLog($request, $response);

        try {
            if (! $response->body()['returnStateInfo']['returnCode'] === '00') {
                throw new ErrorResponse("Synchronize URA product list with the local list: {$response->body()['returnStateInfo']['returnMessage']}", 200);
            }
        } catch (\Exception $error) {
            throw new ErrorResponse("Synchronize URA product list with the local list: {$error->getMessage()}", 200);
        }

        try {
            $decodedContent = KumusoftKakasa::base64Decode($response->body()['data']['content']);
            $parsedContent = json_decode($decodedContent, true);

            return response()->json([
                'status' => 200,
                'data' => $parsedContent,
            ]);
        } catch (\Exception $error) {
            return response()->json([
                'status' => 200,
                'error' => $error->getMessage(),
            ]);
        }
    }

    /**
     * QrCodes
     *
     * @param Request $request
     * @param string $invoice_no
     * @return JsonResponse
     */
    public function QrCode(Request $request, $invoice_no): JsonResponse
    {
        // Request Params
        $content = [
            'invoiceNo' => $invoice_no,
        ];
        $request_data = KumusoftKakasa::prepareRequestData($content, 'T108');

        // Post Data
        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

        $invoice = json_decode(KumusoftKakasa::base64Decode($response->body()['data']['content'], $response->body()['data']['dataDescription']['zipCode']), true);
        $qrCode = fiscalDocument.generateQrCode($invoice['summary']['qrCode']);

        // Generate the Image file
        // await fiscalDocument.generateQrCodeImage($qrCode, $invoice_no);

        // Request URL
        $baseUrl = $request->getSchemeAndHttpHost();

        return response()->json([
            'base64' => $qrCode,
            'image' => '', // Deactivated. Was unstable
        ]);
    }

    /**
     * List of credit notes/debit notes
     *
     * @return JsonResponse
     *
     * @throws ErrorResponse
     */
    public function T111(Request $request): JsonResponse
    {
        // Request Params
        $content = $request->all();
        $request_data = KumusoftKakasa::prepareRequestData($content, 'T111');

        // Post Data
        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

        // Logging endpoint
        LoggerMiddleware::userActivityLog($request, $response);

        try {
            if (! $response->body()['returnStateInfo']['returnCode'] === '00') {
                throw new ErrorResponse("List of credit notes/debit notes: {$response->body()['returnStateInfo']['returnMessage']}", 200);
            }
        } catch (\Exception $error) {
            throw new ErrorResponse("List of credit notes/debit notes: {$error->getMessage()}", 200);
        }

        try {
            $decodedContent = KumusoftKakasa::base64Decode($response->body()['data']['content'], $response->body()['data']['dataDescription']['zipCode']);
            $parsedContent = json_decode($decodedContent, true);

            return response()->json([
                'status' => $response->body()['returnStateInfo'],
                'data' => $parsedContent,
            ]);
        } catch (\Exception $error) {
            return response()->json([
                'status' => $response->body()['returnStateInfo'],
            ]);
        }
    }

    /**
     * Approve CreditNote or DebitNote
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function T113(Request $request): JsonResponse
    {
        $data = [
            'referenceNo' => $request->input('referenceNo'),
            'approveStatus' => $request->input('approveStatus'),
            'taskId' => $request->input('taskId'),
            'remark' => $request->input('remark'),
        ];

        // Request Data
        $request_data = KumusoftKakasa::prepareRequestData($data, 'T113');

        // Post Data
        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

        try {
            $decodedContent = KumusoftKakasa::base64Decode($response->body()['data']['content'], $response->body()['data']['dataDescription']['zipCode']);
            $parsedContent = json_decode($decodedContent, true);

            return response()->json([
                'status' => $response->body()['returnStateInfo'],
                'data' => $parsedContent,
            ]);
        } catch (\Exception $error) {
            return response()->json([
                'status' => $response->body()['returnStateInfo'],
            ]);
        }
    }

    public function T114(Request $req): JsonResponse
    {
        $data = [
            'oriInvoiceId' => $req->input('oriInvoiceId'),
            'invoiceNo' => $req->input('invoiceNo'),
            'reason' => $req->input('reason'),
            'reasonCode' => $req->input('reasonCode'),
            'invoiceApplyCategoryCode' => $req->input('invoiceApplyCategoryCode'),
        ];

        $request_data = KumusoftKakasa::prepareRequestData($data, 'T114');

        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

        try {
            $responseData = json_decode(KumusoftKakasa::base64Decode($response->body()['data']['content'], $response->body()['data']['dataDescription']['zipCode']), true);

            return response()->json([
                'status' => $response->body()['returnStateInfo'],
                'data' => $responseData,
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'status' => $response->body()['returnStateInfo'],
            ], 200);
        }
    }

    public function T118(Request $req, LoggerMiddleware $next)
    {
        $noteId = $req->route('noteId');

        $content = [
            'id' => $noteId,
        ];

        $request_data = KumusoftKakasa::prepareRequestData($content, 'T118');

        $response = Http::post(config('uraefrisapi.taxpayer.OFFLINE_SERVER_URL'), $request_data);

        $next::userActivityLog($req, $response, $next);

        try {
            if ($response->body()['returnStateInfo']['returnCode'] !== '00') {
                return new ErrorResponse('Details of a specified Credit Note: '.$response->body()['returnStateInfo']['returnMessage'], 200);
            }
        } catch (\Exception $error) {
            return new ErrorResponse('Details of a specified Credit Note: '.$error, 200);
        }

        try {
            $responseData = json_decode(KumusoftKakasa::base64Decode($response->body()['data']['content'], $response->body()['data']['dataDescription']['zipCode']), true);
            echo 'Details of a specified Credit Note Response: '.json_encode($responseData);
        } catch (\Exception $error) {
        }

        try {
            return response()->json([
                'status' => $response->body()['returnStateInfo'],
                'data' => json_decode(KumusoftKakasa::base64Decode($response->body()['data']['content'], $response->body()['data']['dataDescription']['zipCode']), true),
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'status' => $response->body()['returnStateInfo'],
            ], 200);
        }
    }
}

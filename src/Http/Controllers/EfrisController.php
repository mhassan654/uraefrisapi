<?php

namespace Mhassan654\Uraefrisapi\Http\Controllers;

use App\Models\EfrisProduct;
use App\Models\KakasaCreditNote;
use http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Log;
use Mhassan654\Uraefrisapi\Exceptions\ErrorResponse;
use Mhassan654\Uraefrisapi\Http\Middleware\LoggerMiddleware;
use Mhassan654\Uraefrisapi\Models\KakasaInvoice;
use Mhassan654\Uraefrisapi\Models\KumusoftKakasa;
use Mhassan654\Uraefrisapi\Services\EfrisDataService;

class EfrisController extends Controller
{
    protected $efrisDataService;
    public function __construct(EfrisDataService $efrisDataService)
    {
        $this->efrisDataService = $efrisDataService;

    }
    /**
     * Get Server Configuration Information
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getServerInfo()
    {
      return  $this->efrisDataService->getServerInfo();
    }

    /**
     * All Exchange Rates
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function T126()
    {
        return  $this->efrisDataService->T126();
    }

    /**
     * The current Server Time.
     * The EFD time is synchronized with the server time.
     * Interface Code: T101
     *
     * @param  \Illuminate\Http\Response  $response
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws ErrorResponse
     */
    public function T101()
    {
        $request_data = KumusoftKakasa::prepareRequestData('', 'T101');

        $response = Http::post(config('taxpayer.OFFLINE_SERVER_URL'), $request_data);

        try {
            if (!$response->json('returnStateInfo.returnCode') === '00') {
                throw new ErrorResponse('Server Time Error: ' . $response->json('returnStateInfo.returnMessage'), 200);
            }
        } catch (\Exception $e) {
            throw new ErrorResponse('Server Time Error: ' . $e->getMessage(), 200);
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
     * Taxpayer Device Registration Details
     * Interface Code: T103
     *
     * @param  \Illuminate\Http\Response  $response
     * @return \Illuminate\Http\JsonResponse
     */
    public function T103()
    {
      return $this->efrisDataService->T103();
    }

    /**
     * Details of a specified invoice
     *
     * @param  \Illuminate\Http\Response  $response
     * @return \Illuminate\Http\JsonResponse
     */
    public function T108(Request $request)
    {
        $invoice = $request->route('invoice_no');
        $content = [
            'invoiceNo' => $invoice,
        ];
        $request_data = KumusoftKakasa::prepareRequestData($content, 'T108');

        $response = Http::post(config('taxpayer.OFFLINE_SERVER_URL'), $request_data);

        try {
            $decodedResponse = json_decode(KumusoftKakasa::base64Decode($response->json('data.content'), $response->json('data.dataDescription.zipCode')), true);
            store()->put($decodedResponse['basicInformation']['invoiceNo'], $decodedResponse);
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
     * Get Taxpayer information by TIN, BRN or NIN
     *
     * @param  \Illuminate\Http\Response  $response
     * @return \Illuminate\Http\JsonResponse
     */
    public function T119(Request $request)
    {
        $content = [
            'tin' => $request->input('tin'),
            'ninBrn' => $request->input('ninBrn'),
        ];
        $request_data = KumusoftKakasa::prepareRequestData($content, 'T119');

        $response = Http::post(config('taxpayer.OFFLINE_SERVER_URL'), $request_data);

        try {
            if (!$response->json('returnStateInfo.returnCode') === '00') {
                throw new ErrorResponse('Get Taxpayer information by TIN, BRN or NIN: ' . $response->json('returnStateInfo.returnMessage'), 200);
            }
        } catch (\Exception $e) {
            throw new ErrorResponse('Get Taxpayer information by TIN, BRN or NIN: ' . $e->getMessage(), 200);
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
     * @param  \Illuminate\Http\Response  $response
     * @return \Illuminate\Http\JsonResponse
     */
    public function T121(Request $request)
    {
        return $this->efrisDataService->T121($request);
    }

    /**
     * Query the stock quantity by goods ID
     *
     * @param  \Illuminate\Http\Response  $response
     * @return \Illuminate\Http\JsonResponse
     */
    public function T128(Request $request)
    {
        return $this->efrisDataService->T128($request);
    }

    /**
     * Goods/Services query by product code
     *
     * @param  \Illuminate\Http\Response  $response
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws ErrorResponse
     */
    public function T144(Request $request)
    {
        $content = [
            'goodsCode' => $request->input('goodsCode'),
        ];
        $request_data = KumusoftKakasa::prepareRequestData($content, 'T144');

        $response = Http::post(config('taxpayer.OFFLINE_SERVER_URL'), $request_data);

        try {
            if (!$response->json('returnStateInfo.returnCode') === '00') {
                throw new ErrorResponse('Goods/Services query by product code: ' . $response->json('returnStateInfo.returnMessage'), 200);
            }
        } catch (\Exception $e) {
            throw new ErrorResponse('Goods/Services query by product code: ' . $e->getMessage(), 200);
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
     * @param  \Illuminate\Http\Response  $response
     * @return \Illuminate\Http\JsonResponse
     */
    public function T176(Request $request)
    {
        $request_data = KumusoftKakasa::prepareRequestData('', 'T176');

        $response = Http::post(config('taxpayer.OFFLINE_SERVER_URL'), $request_data);

        try {
            if (!$response->json('returnStateInfo.returnCode') === '00') {
                throw new ErrorResponse('EFRIS Dictionary/Dropdowns: ' . $response->json('returnStateInfo.returnMessage'), 200);
            }
        } catch (\Exception $e) {
            throw new ErrorResponse('EFRIS Dictionary/Dropdowns: ' . $e->getMessage(), 200);
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
     * @return \Illuminate\Http\Response
     */
    public function T115(Request $req, Response $res)
    {
        $request_data = KumusoftKakasa::prepareRequestData('', 'T115');

        // Post Data
        $response = Http::withOptions([
            'verify' => false,
            'json' => true,
        ])->post(taxpayer::OFFLINE_SERVER_URL, $request_data);

        // Logging endpoint
        LoggerMiddleware::userActivityLog($req, $response, function ($req, $res) {
        });

        try {
            if (!$response->body['returnStateInfo']['returnCode'] === '00') {
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
     * @return \Illuminate\Http\Response
     */
    public function T130(Request $req, Response $res)
    {
        $product = KumusoftKakasa::prepareInventoryData($req->input('products'));
        Log::info($req->input());
        $request_data = KumusoftKakasa::prepareRequestData($product, 'T130');

        // Post Data
        $response = Http::post(taxpayer::OFFLINE_SERVER_URL, $request_data);

        // Logging endpoint
        LoggerMiddleware::userActivityLog($req, $response, function ($req, $res) {
        });

        try {
            if (!$response->body['returnStateInfo']['returnCode'] === '00') {
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
                Host::post(taxpayer::KUMUSOFT_MIDDLEWARE_URL . '/sync-products')
                    ->then(function ($response) {
                        Log::info($response);
                    })
                    ->catch(function ($error) {
                        Log::info($error);
                    });

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
     * @return \Illuminate\Http\Response
     */
    public function T107(Request $req, Response $res)
    {
        $content = [
            'invoiceNo' => $req->input('invoiceNo'),
            'deviceNo' => taxpayer::DEVICE_NO,
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
        $response = Http::post(taxpayer::OFFLINE_SERVER_URL, $request_data);

        // Logging endpoint
        LoggerMiddleware::userActivityLog($req, $response, function ($req, $res) {
        });

        try {
            if (!$response->body['returnStateInfo']['returnCode'] === '00') {
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

    public function T109Invoice(Request $req, Response $res)
    {
        $data = $req->input();

        $EfrisInvoice = new Invoice($data);
        $product = $EfrisInvoice->prepareInvoiceDetails($data);
        $request_data = KumusoftKakasa::prepareRequestData($product, 'T109');

        // Post Data
        $response = Http::post(taxpayer::OFFLINE_SERVER_URL, $request_data);

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
                $response = Http::post(taxpayer::OFFLINE_SERVER_URL, [
                    'url' => taxpayer::OFFLINE_SERVER_URL,
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
    public function T109(Request $request)
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

            $response = Http::post(taxpayer::OFFLINE_SERVER_URL, [
                'url' => taxpayer::OFFLINE_SERVER_URL,
                'body' => $request_data,
                'json' => true,
            ]);

            LoggerMiddleware::userActivityLog($request, $response, $next);

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
    public function T109Preview(Request $request)
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
                    'operationType' => '101',
                    //Increase
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

            $response = Http::post(taxpayer::OFFLINE_SERVER_URL, [
                'url' => taxpayer::OFFLINE_SERVER_URL,
                'body' => $request_data,
                'json' => true,
            ]);

            LoggerMiddleware::userActivityLog($request, $response, $next);

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
    public function T131down(Request $request)
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
                    'operationType' => '102',
                    //Decrease
                    'adjustType' => $request->input('adjustType'),
                    'remarks' => $request->input('remarks'),
                ],
                'goodsStockInItem' => $stockItems,
            ];

            $request_data = KumusoftKakasa::prepareIncreaseRequestData($data, 'T131');

            $response = Http::post(taxpayer::OFFLINE_SERVER_URL, [
                'url' => taxpayer::OFFLINE_SERVER_URL,
                'body' => $request_data,
                'json' => true,
            ]);

            LoggerMiddleware::userActivityLog($request, $response, $next);

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function t124(Request $request)
    {
        $data = [
            'pageNo' => $request->input('pageNo'),
            'pageSize' => $request->input('pageSize'),
        ];

        $request_data = KumusoftKakasa::prepareRequestData($data, 'T124');

        $response = Http::post(taxpayer::OFFLINE_SERVER_URL, $request_data);

        // Logging endpoint
        LoggerMiddleware::userActivityLog($request, $response);

        try {
            if (!$response->body['returnStateInfo']['returnCode'] === '00') {
                return response()->json([
                    'message' => 'Pick the UNSPSC: ' . $response->body['returnStateInfo']['returnMessage'],
                ], 200);
            }

            Log::info(KumusoftKakasa::base64Decode($response->body['data']));
        } catch (\Exception $error) {
            return response()->json([
                'message' => 'Pick the UNSPSC: ' . $error->getMessage(),
            ], 200);
        }
    }

    /**
     * Pick UNSPSC codes
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function t124codes()
    {
        $results = unspscCode::all();

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function t124Unspsc(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $pagination = $request->query('pagination', 300);

            $startIndex = ($page - 1) * $pagination;
            $endIndex = $page * $pagination;

            $unspscs = unspscCode::skip($startIndex)->take($pagination)->get();

            $count = unspscCode::count();

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function T125()
    {
        $request_data = KumusoftKakasa::prepareRequestData('', 'T125');

        $response = Http::post(taxpayer::OFFLINE_SERVER_URL, $request_data);

        // Logging endpoint
        LoggerMiddleware::userActivityLog($request, $response);

        try {
            if (!$response->body['returnStateInfo']['returnCode'] === '00') {
                return response()->json([
                    'message' => 'Inquire info about Excercise Duty: ' . $response->body['returnStateInfo']['returnMessage'],
                ], 200);
            }
        } catch (\Exception $error) {
            return response()->json([
                'message' => 'Inquire info about Excercise Duty: ' . $error->getMessage(),
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function T127(Request $request)
    {
      return $this->efrisDataService->T127($request);
    }

    /**
     * Invoice Inquiry
     * Query all invoice information(Invoice /receipt CreditNode ,Debit Node,Cancel CreditNode ,Debit Node)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function T106(Request $request)
    {
      return  $this->efrisDataService->T106($request);      
    }

    /**
     * Generate a credit note application
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function T110(Request $request)
    {
        $postedItems = $request->all();

        // 1. Get Invoice Details
        $invoice = $postedItems['generalInfo']['oriInvoiceNo'];

        // Pick the original invoice from URA
        $original_invoice = function () use ($invoice) {
            return Http::get(taxpayer::KUMUSOFT_MIDDLEWARE_URL . '/invoice-details/' . $invoice)->json()['data'];
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function manufacturerStockIn(Request $request)
    {
        $manufacturing = $request->input('data');

        // 1. List of raw materials data be reduced from stock
        $reduce_rawmaterials = [
            'goodsStockIn' => [
                'operationType' => '102',
                // Decrease
                'adjustType' => '104',
                // Raw materials
                'remarks' => $manufacturing['remarks'],
            ],
            'goodsStockInItem' => EfrisProduct::prepareT131ProductList($manufacturing['rawMaterials']),
        ];

        // 2. Request data for the raw materials
        $rawmaterials_requestdata = KumusoftKakasa::prepareRequestData($reduce_rawmaterials, 'T131');

        // 3. List of finished products
        $increase_finishedproducts = [
            'goodsStockIn' => [
                'operationType' => '101',
                // Increase
                'remarks' => $manufacturing['remarks'],
                'stockInDate' => $manufacturing['stockInDate'],
                'stockInType' => '103',
                // Manufacturing
                'productionBatchNo' => $manufacturing['productionBatchNo'],
                'productionDate' => $manufacturing['productionDate'],
            ],
            'goodsStockInItem' => EfrisProduct::prepareT131ProductList($manufacturing['finalProducts']),
        ];

        // 4. Request data for the finished products
        $finishedproducts_requestdata = KumusoftKakasa::prepareRequestData($increase_finishedproducts, 'T131');

        // 5. Send Reduction request to server
        Http::post(taxpayer::OFFLINE_SERVER_URL, $rawmaterials_requestdata);

        // 6. Send Increment request to server
        $response = Http::post(taxpayer::OFFLINE_SERVER_URL, $finishedproducts_requestdata);

        LoggerMiddleware::userActivityLog($request, $response);

        try {
            if ($response->body()['returnStateInfo']['returnCode'] !== '00') {
                throw new errorResponse("Record stock for manufacturers: {$response->body()['returnStateInfo']['returnMessage']}", 200);
            }
        } catch (\Exception $error) {
            throw new ErrorResponse("Record stock for manufacturers: {$error->getMessage()}", 200);
        }

        try {
            $decodedContent = KumusoftKakasa . base64Decode($response->body()['data']['content']);
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function synchProductsDatabase(Request $request)
    {
       $this->efrisDataService->synchProductsDatabase($request);
    }

    /**
     * QrCodes
     *
     * @param  string  $invoice_no
     * @return \Illuminate\Http\JsonResponse
     */
    public function QrCode(Request $request, $invoice_no)
    {
        // Request Params
        $content = [
            'invoiceNo' => $invoice_no,
        ];
        $request_data = KumusoftKakasa::prepareRequestData($content, 'T108');

        // Post Data
        $response = Http::post(taxpayer::OFFLINE_SERVER_URL, $request_data);

        $invoice = json_decode(KumusoftKakasa::base64Decode($response->body()['data']['content'], $response->body()['data']['dataDescription']['zipCode']), true);
        $qrCode = fiscalDocument . generateQrCode($invoice['summary']['qrCode']);

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
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws ErrorResponse
     */
    public function T111(Request $request)
    {
        return $this->efrisDataService->T111($request);
    }

    /**
     * Approve CreditNote or DebitNote
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function T113(Request $request)
    {
       return $this->efrisDataService->T113($request);
    }

    public function T114(Request $req)
    {
        return $this->efrisDataService->T114($req);
    }

    public function T118(Request $req, Response $res, LoggerMiddleware $next)
    {
        $noteId = $req->route('noteId');

        $content = [
            'id' => $noteId,
        ];

        $request_data = KumusoftKakasa::prepareRequestData($content, 'T118');

        $response = Http::post(taxpayer::OFFLINE_SERVER_URL, $request_data);

        $next::userActivityLog($req, $response, $next);

        try {
            if ($response->body()['returnStateInfo']['returnCode'] !== '00') {
                return new ErrorResponse('Details of a specified Credit Note: ' . $response->body()['returnStateInfo']['returnMessage'], 200);
            }
        } catch (\Exception $error) {
            return new ErrorResponse('Details of a specified Credit Note: ' . $error, 200);
        }

        try {
            $responseData = json_decode(KumusoftKakasa::base64Decode($response->body()['data']['content'], $response->body()['data']['dataDescription']['zipCode']), true);
            echo 'Details of a specified Credit Note Response: ' . json_encode($responseData);
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
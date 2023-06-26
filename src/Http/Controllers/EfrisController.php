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
use Mhassan654\Uraefrisapi\Models\UnspscCode;
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
       return $this->efrisDataService->T101();
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
       return $this->efrisDataService->T108($request);
    }

    

    /**
     * Get Taxpayer information by TIN, BRN or NIN
     *
     * @param  \Illuminate\Http\Response  $response
     * @return \Illuminate\Http\JsonResponse
     */
    public function T119(Request $request)
    {
       return $this->T119($request);
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
       return $this->efrisDataService->T144($request);
    }

    /**
     * EFRIS Dictionary/Dropdowns
     *
     * @param  \Illuminate\Http\Response  $response
     * @return \Illuminate\Http\JsonResponse
     */
    public function T176(Request $request)
    {
      return $this->efrisDataService->T176($request);
    }

    /**
     * EFRIS Dictionary/Dropdowns
     *
     */
    public function T115(Request $req)
    {
        return $this->efrisDataService->T115($req);
    }

    /**
     * Register a product or Service
     *
     */
    public function T130(Request $req)
    {
        return $this->efrisDataService->T130($req);
    }

    /**
     * Active Invoices {Those which can be issued credit/debit notes}
     * T107
     *
     * @return \Illuminate\Http\Response
     */
    public function T107(Request $req)
    {
        return $this->efrisDataService->T107($req);
    }

    public function T109Invoice(Request $req)
    {
        return $this->efrisDataService->T109Invoice($req);
    }

    /**
     * Create invoices or Receipts in Bulk
     */
    public function T109Bulk(Request $request)
    {
        return $this->efrisDataService->T109Bulk($request);
    }

    /**
     * Create an Invoice or Receipt
     * Invoices for VAT registered taxpayers, Receipts for non-VAT registered taxpayers
     */
    public function T109(Request $request)
    {
        return $this->efrisDataService->T109($request);
    }

    /**
     * Create an Invoice or Receipt Preview
     * Invoices for VAT registered taxpayers, Receipts for non-VAT registered taxpayers
     */
    public function T109Preview(Request $request)
    {
        return $this->efrisDataService->T109Preview($request);
    }

    /**
     * Increase Stock for a given Item
     *
     * @return string
     */
    public function T131up(Request $request)
    {
        return $this->efrisDataService->T131up($request);
    }

    /**
     * Decrease stock of a given item
     */
    public function T131down(Request $request)
    {
       return $this->efrisDataService->T131down($request);
    }

    /**
     * Pick the UNSPSC
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function T124(Request $request)
    {
       return $this->efrisDataService->T124($request);
    }

    /**
     * Pick UNSPSC codes
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function T124codes()
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
     */
    public function T124Unspsc(Request $request)
    {
       return $this->efrisDataService->T124Unspsc($request);
    }

    /**
     * Inquire info about Excercise Duty
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function T125()
    {
        $this->efrisDataService->T125();
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
       return $this->efrisDataService->T110($request);
    }

    /**
     * Record stock for manufacturers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function manufacturerStockIn(Request $request)
    {
        return $this->efrisDataService->manufacturerStockIn($request);
    }

    /**
     * Synchronize URA product list with the local list
     *
     */
    public function synchProductsDatabase(Request $request)
    {
      return  $this->efrisDataService->synchProductsDatabase($request);
    }

    /**
     * QrCodes
     *
     * @param  string  $invoice_no
     * @return \Illuminate\Http\JsonResponse
     */
    public function QrCode(Request $request, $invoice_no)
    {
       return $this->efrisDataService->QrCode($request, $invoice_no);
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

    public function T118(Request $req, LoggerMiddleware $next)
    {
        return $this->efrisDataService->T118($req, $next);
    }
}
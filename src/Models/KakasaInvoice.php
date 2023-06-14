<?php

namespace Mhassan654\Uraefrisapi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mhassan654\Uraefrisapi\Helpers\ArrayHelper;

class KakasaInvoice extends Model
{

    /**
     * Request Data
     */
    private $requestData = [];

    /**
     * Which type of invoice is this?
     */
    private $_invoiceCategory = '';

    /**
     * Create an instance of an invoice/creditnote/receipt
     * @param array $data
     * @param string $category
     */
    public function __construct(array $data, string $category = 'DT')
    {
        $this->requestData = $data;
        $this->_invoiceCategory = $category;
    }

    /**
     * Prepare original invoice data
     * - Previous invoice is required
     * @param array $originalInvoice
     * @return array
     */
    public function prepareCreditNoteInfo($originalInvoice): array
    {
        return [
            "oriInvoiceId" => $originalInvoice["basicInformation"]["invoiceId"],
            "oriInvoiceNo" => $this->requestData["generalInformation"]["invoice_no"],
            "reasonCode" => $this->requestData["generalInformation"]["reasonCode"],
            "reason" => $this->requestData["generalInformation"]["reason"],
            "applicationTime" => KumusoftKakasa::currentDateAndTime(),
            "invoiceApplyCategoryCode" => "101", //Credit Note
            "currency" => $originalInvoice["basicInformation"]["currency"],
            "contactName" => $this->requestData["generalInformation"]["contactName"],
            "contactMobileNum" => $this->requestData["generalInformation"]["contactMobileNum"],
            "contactEmail" => $this->requestData["generalInformation"]["contactEmail"],
            "source" => "103",
            "remarks" => $this->requestData["generalInformation"]["remarks"],
            "sellersReferenceNo" => $this->requestData["generalInformation"]["sellersReferenceNo"]
        ];
    }

    /**
     * Generate Request info in EFRIS format
     */
    public function prepareInvoiceDetails($data)
    {
        $invoiceProducts = $this->prepareProductDetails($data['basicInformation']['invoiceIndustryCode']);

        if ($invoiceProducts['hasErrors'] === 1) {
            return $invoiceProducts;
        } else {
            return [
                "sellerDetails" => [
                    "tin" => $taxpayer->TIN,
                    "ninBrn" => $taxpayer->BRN,
                    "legalName" => $taxpayer->LEGAL_NAME,
                    "businessName" => $taxpayer->BIZ_NAME,
                    "address" => $taxpayer->ADDRESS,
                    "mobilePhone" => $taxpayer->MOBILE_PHONE,
                    "linePhone" => $taxpayer->LINE_PHONE,
                    "emailAddress" => $taxpayer->EMAIL,
                    "placeOfBusiness" => $data['sellerDetails']['placeOfBusiness'] ?? $taxpayer->ADDRESS,
                    "referenceNo" => $data['sellerDetails']['referenceNo'],
                    "branchId" => $data['sellerDetails']['branchId'] ?? null
                ],
                "basicInformation" => [
                    "invoiceNo" => $data['basicInformation']['invoiceNo'],
                    "antifakeCode" => "",
                    "deviceNo" => $taxpayer->DEVICE_NO,
                    "issuedDate" => KumusoftKakasa::currentDateAndTime(),
                    "operator" => $data['basicInformation']['operator'],
                    "currency" => $data['basicInformation']['currency'],
                    "oriInvoiceId" => "",
                    "invoiceType" => 1,
                    "invoiceKind" => ($taxpayer->IS_VAT_REGISTERED == 101) ? 1 : 2,
                    "dataSource" => $taxpayer->DATA_SOURCE,
                    "invoiceIndustryCode" => $data['basicInformation']['invoiceIndustryCode'] ?? (string)$taxpayer->INDUSTRY_CODE,
                    "isBatch" => "0"
                ],
                "buyerDetails" => [
                    "buyerTin" => $data['buyerDetails']['buyerTin'],
                    "buyerNinBrn" => $data['buyerDetails']['buyerNinBrn'],
                    "buyerPassportNum" => $data['buyerDetails']['buyerPassportNum'],
                    "buyerLegalName" => $data['buyerDetails']['buyerLegalName'],
                    "buyerBusinessName" => $data['buyerDetails']['buyerBusinessName'],
                    "buyerAddress" => $data['buyerDetails']['buyerAddress'],
                    "buyerEmail" => $data['buyerDetails']['buyerEmail'],
                    "buyerMobilePhone" => $data['buyerDetails']['buyerMobilePhone'],
                    "buyerLinePhone" => $data['buyerDetails']['buyerLinePhone'],
                    "buyerPlaceOfBusi" => "",
                    "buyerType" => $data['buyerDetails']['buyerType'],
                    "buyerCitizenship" => "",
                    "buyerSector" => "",
                    "buyerReferenceNo" => ""
                ],
                "goodsDetails" => $invoiceProducts,
                "taxDetails" => self::getTaxDetails($data['basicInformation']['invoiceIndustryCode']),
                "summary" => $this->getInvoiceSummary($data),
                "payWay" => [],
                "extend" => []
            ];
        }
    }

    /**
     * Prepare details needed to generate a receipt
     * @param array $data
     * @return array
     */
    public function prepareReceiptDetails($data)
    {
        return [
            "sellerDetails" => [
                "tin" => config('uraefrisapi.TIN'),
                "ninBrn" => config('uraefrisapi.BRN'),
                "legalName" => config('uraefrisapi.LEGAL_NAME'),
                "businessName" => config('uraefrisapi.BIZ_NAME'),
                "address" => config('uraefrisapi.ADDRESS'),
                "mobilePhone" => config('uraefrisapi.MOBILE_PHONE'),
                "linePhone" => config('uraefrisapi.LINE_PHONE'),
                "emailAddress" => config('uraefrisapi.EMAIL'),
                "placeOfBusiness" => $data['sellerDetails']['placeOfBusiness'],
                "referenceNo" => $data['sellerDetails']['referenceNo']
            ],
            "basicInformation" => [
                "invoiceNo" => $data['basicInformation']['invoiceNo'],
                "antifakeCode" => "",
                "deviceNo" => config('uraefrisapi.DEVICE_NO'),
                "issuedDate" => KumusoftKakasa::currentDateAndTime(),
                "operator" => $data['basicInformation']['operator'],
                "currency" => $data['basicInformation']['currency'],
                "oriInvoiceId" => "",
                "invoiceType" => 1,
                "invoiceKind" => $this->setInvoiceKind(),
                "dataSource" => config('uraefrisapi.DATA_SOURCE'),
                "invoiceIndustryCode" =>config('uraefrisapi.INDUSTRY_CODE'),
                "isBatch" => "0"
            ],
            "buyerDetails" => [
                "buyerTin" => $data['buyerDetails']['buyerTin'],
                "buyerNinBrn" => $data['buyerDetails']['buyerNinBrn'],
                "buyerPassportNum" => $data['buyerDetails']['buyerPassportNum'],
                "buyerLegalName" => $data['buyerDetails']['buyerLegalName'],
                "buyerBusinessName" => $data['buyerDetails']['buyerBusinessName'],
                "buyerAddress" => $data['buyerDetails']['buyerAddress'],
                "buyerEmail" => $data['buyerDetails']['buyerEmail'],
                "buyerMobilePhone" => $data['buyerDetails']['buyerMobilePhone'],
                "buyerLinePhone" => $data['buyerDetails']['buyerLinePhone'],
                "buyerPlaceOfBusi" => "",
                "buyerType" => $data['buyerDetails']['buyerType'],
                "buyerCitizenship" => "",
                "buyerSector" => "",
                "buyerReferenceNo" => ""
            ],
            "goodsDetails" => self::prepareProductDetails(),
            "taxDetails" => $this->getTaxDetails(),
            "summary" => $this->getInvoiceSummary($data),
            "payWay" => [],
            "extend" => [],
            "importServicesSeller" => [
                "importBusinessName" => "",
                "importEmailAddress" => "",
                "importContactNumber" => "",
                "importAddres" => "",
                "importInvoiceDate" => "",
                "importAttachmentName" => "",
                "importAttachmentContent" => ""
            ],
            "summaryInfo" => [
                "remarks" => $data['remarks']
            ]
        ];
    }

    /**
     * Generate Request info to generate a customs invoice
     */
    public function prepareCustomsInvoiceDetails($data)
    {
        return [
            "basicInformation" => [
                "invoiceNo" => $data['basicInformation']['invoiceNo'],
                "antifakeCode" => "",
                "deviceNo" => config('uraefrisapi.DEVICE_NO'),
                "operator" => $data['basicInformation']['operator'],
                "currency" => $data['basicInformation']['currency'],
                "sadNumber" => $data['basicInformation']['sadNumber'],
                "sadDate" => $data['basicInformation']['sadDate'],
                "wareHouseNumber" => $data['basicInformation']['wareHouseNumber'],
                "wareHouseName" => $data['basicInformation']['wareHouseName'],
                "office" => $data['basicInformation']['office'],
                "cif" => $data['basicInformation']['cif'],
                "valuationMethod" => $data['basicInformation']['valuationMethod']
            ],
            "buyerDetails" => [
                "buyerTin" => $data['buyerDetails']['buyerTin'],
                "buyerNinBrn" => $data['buyerDetails']['buyerNinBrn'],
                "buyerPassportNum" => $data['buyerDetails']['buyerPassportNum'],
                "buyerLegalName" => $data['buyerDetails']['buyerBusinessName'],
                "buyerBusinessName" => $data['buyerDetails']['buyerBusinessName'],
                "buyerAddress" => $data['buyerDetails']['buyerAddress'],
                "buyerEmail" => $data['buyerDetails']['buyerEmail'],
                "buyerMobilePhone" => $data['buyerDetails']['buyerMobilePhone'],
                "buyerLinePhone" => $data['buyerDetails']['buyerLinePhone'],
                "buyerPlaceOfBusi" => ""
            ],
            "goodsDetails" => [
                [
                    "item" => "apple",
                    "itemCode" => "101",
                    "qty" => "2",
                    "unitOfMeasure" => "kg",
                    "unitPrice" => "150.00",
                    "total" => "1",
                    "taxRate" => "0.18",
                    "tax" => "12.88",
                    "orderNumber" => "0",
                    "hsCode" => "5467",
                    "hsCodeDescription" => "Test",
                    "pack" => "1 Box"
                ]
            ],
            "taxDetails" => [
                [
                    "taxCategory" => "'Standard",
                    "netAmount" => "3813.55",
                    "taxRate" => "0.18",
                    "taxAmount" => "686.45",
                    "grossAmount" => "4500.00",
                    "taxRateName" => "123"
                ],
                [
                    "taxCategory" => "Excise Duty",
                    "netAmount" => "1818.18",
                    "taxRate" => "0.1",
                    "taxAmount" => "181.82",
                    "grossAmount" => "2000.00",
                    "taxRateName" => "123"
                ]
            ],
            "summary" => [
                "netAmount" => "8379",
                "taxAmount" => "868",
                "grossAmount" => "9247",
                "itemCount" => "5",
                "remarks" => $data['remarks'],
                "qrCode" => "asdfghjkl",
                "prn" => "201905081234"
            ],
            "extend" => [],
            "summaryInfo" => [
                "remarks" => $data['remarks']
            ]
        ];
    }

    /**
     * Items bought
     *
     * @param array $items
     */
    public function prepareProductDetails($industryCode)
    {
        $itemsBought = [];
        $itemsNotFound = [];
        $itemOrderNum = 0;

        foreach ($this->requestData['itemsBought'] as $itemData) {
            $goods = new KakasaProduct($itemData, $industryCode);

            if ($goods->item && $goods->itemCode) {
                $goods->orderNumber = $itemOrderNum;

                unset($goods->taxCategory);
                unset($goods->taxRateName);
                if (isset($goods->stick)) {
                    unset($goods->stick);
                }

                if (isset($goods->pack)) {
                    unset($goods->pack);
                }

                if (isset($goods->exciseUnit)) {
                    unset($goods->exciseUnit);
                }

            $goods->tax = $this->getTaxAmount($goods->total, $goods->taxRate);
            $goods->netAmount = (float)$goods->total - $goods->tax;
            $goods->grossAmount = (float)$goods->total;
            $goods->discountTotal = (float)$goods->discountTotal > 0 ? $goods->discountTotal : "";

            $itemsBought[] = $goods;

            if ($goods->discountFlag == "1") {
                $itemOrderNum += 1;
                $discountLine = $goods->discountLine;
                $discountLine->orderNumber = $itemOrderNum;
                $discountLine->taxRate = $goods->taxRate;
                $discountLine->tax = (float)$discountLine->taxRate ? $this->getTaxAmount($discountLine->total, $discountLine->taxRate) : 0;
                $discountLine->discountFlag = 0;
                $discountLine->netAmount = 0;

                $itemsBought[] = $discountLine;

                unset($goods->discountLine);
                unset($goods->discountTotal);
                $goods->discountTotal = $discountLine->total;
                $goods->discountFlag = 1;
            }

            $itemOrderNum += 1;
        } else {
                $itemsNotFound['hasErrors'] = 1;
                $itemsNotFound[] = "Product/Service with code '{$this->requestData['itemsBought']['itemCode']}' not found at EFRIS.";
                Log::info((string)$itemsNotFound);
            }
        }

        if (count($itemsNotFound) > 0) {
            return $itemsNotFound;
        } else {
            $itemsBought['hasErrors'] = 0;
            return $itemsBought;
        }
    }

    /**
     * Get the total number of products in the invoice
     */
    public function getTotalInvoiceProducts($industryCode): int
    {
        $products = $this->prepareProductDetails($industryCode);
        return count($products);
    }

    /**
     * Get tax details for the payway section of the invoice
     */
    public function getTaxDetails($industryCode)
    {
        $itemsBought = [];
        $exciseTotal = [];
        $vatTotal = [];

        foreach ($this->requestData['itemsBought'] as $itemData) {
            $goods = new KakasaProduct($itemData, $industryCode);
            $goods->tax = $this->getTaxAmount($goods->total, $goods->taxRate);
            $goods->netAmount = (float)$goods->total - $goods->tax;
            $goods->grossAmount = (float)$goods->total;

            $grossAmnt = $goods->discountFlag == "1" ? (float)($goods->grossAmount - abs($goods->discountLine->total)) : (float)$goods->grossAmount;
            $discountVat = $goods->discountFlag == "1" ? (abs($goods->discountLine->total) / $goods->total * $goods->tax) : 0;

            $itemsBought[] = [
                "taxCategoryCode" => $goods->taxCategoryCode,
                "isExempt" => $goods->isExempt,
                "isZeroRate" => (intval($industryCode) === 102) ? "101" : $goods->isZeroRate,
                "taxCategory" => $goods->taxCategory,
                "netAmount" => (float)($grossAmnt - $goods->tax + $discountVat),
                "taxRate" => $goods->taxRate,
                "taxAmount" => $goods->discountFlag == "1" ? (float)($goods->tax - $discountVat) : (float)$goods->tax,
                "grossAmount" => (float)$grossAmnt,
                "exciseCurrency" => $goods->exciseCurrency,
                "taxRateName" => $goods->taxRateName
            ];

            if ($goods->exciseFlag == "1") {
                $exciseDiscount = $goods->discountFlag == "1" ? (float)$goods->discountLine->exciseTax : 0;

                $itemsBought[] = [
                    "exciseCurrency" => $goods->exciseCurrency,
                    "exciseUnit" => $goods->exciseUnit,
                    "grossAmount" => (float)$goods->netAmount,
                    "netAmount" => (float)($goods->netAmount - $goods->exciseTax - $exciseDiscount),
                    "taxAmount" => $goods->discountFlag == "1" ? (float)($goods->exciseTax + $exciseDiscount) : (float)$goods->exciseTax,
                    "taxCategory" => "E: Excise Duty",
                    "taxCategoryCode" => "05",
                    "taxRate" => (float)$goods->exciseRate,
                    "taxRateName" => $goods->exciseRateName
                ];
            }
        }

        return $itemsBought;
    }

    /**
     * Create the invoice summary section
     */
    public function getInvoiceSummary($data)
    {
        $taxes = $this->getTaxDetails($data['basicInformation']['invoiceIndustryCode']);
        $arr = [];
        foreach ($taxes as $tax) {
            $taxCode = $tax['taxCategoryCode'];
            if ($taxCode == "04") {
                $tax['taxAmount'] = 0;
                $tax['grossAmount'] = $tax['netAmount'];
            }
            if ($taxCode != "05") {
                $arr[] = $tax;
            }
        }

        $netAmount = (float)(ArrayHelper::getArraySum($arr, 'grossAmount')) - (float)(ArrayHelper::getArraySum($taxes, 'taxAmount'));
        $taxAmount = ArrayHelper::getArraySum($taxes, 'taxAmount');
        $grossAmount = (float)ArrayHelper::getArraySum($arr, 'grossAmount');
        $itemCount = count($this->requestData['itemsBought']);
        $modeCode = "1"; // Issuing (0)Offline, or (1)Online?
        $remarks = $data['remarks'];
        $qrCode = "";

        $summary = [
            "netAmount" => number_format($netAmount, 2),
            "taxAmount" => number_format($taxAmount, 2),
            "grossAmount" => number_format($grossAmount, 2),
            "itemCount" => $itemCount,
            "modeCode" => $modeCode,
            "remarks" => $remarks,
            "qrCode" => $qrCode
        ];

        return $summary;
    }

    /**
     * Get details of an invoice issued earlier
     */
    public function getInvoiceDetails()
    {
        $url  =config('uraefrisapi.KUMUSOFT_MIDDLEWARE_URL')  . '/invoice-details/' . $this->generalInformation['invoice_no'];

        $response = Http::get($url);
        $invoice_details = $response->json();

        return $invoice_details;
    }

    /**
     * Calculate the TaxAmount
     *
     * @param float $totalPrice
     * @param float $taxRate
     * @return float
     */
    public static function getTaxAmount($totalPrice, $taxRate) {
        $tax_amount = 0;
        if ($taxRate === '-' || $taxRate === 0) {
            $tax_amount = 0;
        } else {
            $netPrice = $totalPrice / ($taxRate + 1);
            $tax_amount = round(($totalPrice - $netPrice), 2);
        }
        return $tax_amount;
    }

    /**
     * Find an invoice issued earlier via EFRIS
     *
     * @param int $fiscalNumber
     * @return mixed
     */
    public static function getEfrisInvoice($fiscalNumber) {
        return EfrisInvoice::where('invoiceNo', $fiscalNumber)->first();
    }

    /**
     * Get total price based on the discount flag
     *
     * @param string $goodorservice
     * @return float
     */
    public static function getTotalPrice($goodorservice) {
        $totalPrice = 0;
        switch ($goodorservice) {
            // Whole price is discounted
            case "0":
                $totalPrice = 0;
                break;
            // Part of the price is discounted
            case "1":
                $totalPrice = ($goodorservice->qty * $goodorservice->unitPrice) - $goodorservice->discountTotal;
                break;
            // No discount
            default:
            case "2":
                $totalPrice = $goodorservice->qty * $goodorservice->unitPrice;
                break;
        }
        return $totalPrice;
    }

}

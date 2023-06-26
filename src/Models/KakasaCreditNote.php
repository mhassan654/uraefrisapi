<?php
namespace Mhassan654\Uraefrisapi\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Log;

class KakasaCreditNote extends Model
{
    use HasFactory;

    /**
     * The original invoice number
     */
    public $oriInvoiceNumber = '';

    /**
     * Products and services in this credit note
     */
    private $_productServices = [];

    /**
     * Details of the original invoice
     */
    public $invoiceDetails = [];

    /**
     * General information
     */
    public $genInfo = [];

    /*** reasonCode */
    public const REASONCODE_EXPIRED = 101;

    public const REASONCODE_CANCELLED = 102;

    public const REASONCODE_WRONG_AMOUNT = 103;

    public const REASONCODE_PARTIAL_WAIVEOFF = 104;

    public const REASONCODE_OTHERS = 105;

    /*** invoiceApplyCategoryCode */
    public const INVOICE_APPLY_CODE_CREDITNOTE = 101;

    /*** source */
    public const SOURCE_EFD = 101;

    public const SOURCE_CS = 102;

    public const SOURCE_WEBSERVICE = 103;

    public const SOURCE_BS = 104;

    protected $fillable=[
        'oriInvoiceId',
        'oriInvoiceNo',
        'reasonCode',
        'reason',
        'applicationTime',
        'invoiceApplyCategoryCode',
        'currency',
        'contactName',
        'contactMobileNum',
        'contactEmail',
        'source',
        'remarks',
        'sellersReferenceNo',
        'taxDetails',
        'summary',,
        'goodsDetails',
        ''

    ];

    /**
     * Create an instance of a credit note
     *
     * @param  array  $products
     * @param  array  $info
     * @param  array  $originalInvoice
     */
    public function __construct($products, $info, $originalInvoice)
    {
        parent::__construct($products);
        $this->_productServices = $products;
        $this->genInfo = $info;
        $this->oriInvoiceNumber = $info['oriInvoiceNo'];
        $this->taxpayer = config('uraefrisapi.taxpayer'); // Assuming the taxpayer configuration is stored in the "config" directory
        $this->oriInvoice = $originalInvoice;
        $this->oriInvoiceGoodsDetails = $originalInvoice['goodsDetails'];
    }

    /**
     * The details of the original Invoice
     */
    public function getEfrisInvoice()
    {
        return $this->oriInvoice;
    }

    public function getLocalInvoiceDetails()
    {
        return store()->get($this->genInfo['oriInvoiceNo']);
    }

    /**
     * Build the details of the credit note
     * We build the credit note by picking an invice and changing out some of the parts
     */
    public function buildCreditNote()
    {
        $invoice = $this->getEfrisInvoice();
        $invoice['oriInvoiceId'] = $invoice['basicInformation']['invoiceId'];
        $invoice['oriInvoiceNo'] = $this->genInfo['oriInvoiceNo'];
        $invoice['reasonCode'] = $this->genInfo['reasonCode'];
        $invoice['reason'] = $this->genInfo['reason'];
        $invoice['applicationTime'] = Carbon::now()->format('Y-m-d H:i:s');
        $invoice['invoiceApplyCategoryCode'] = $this->genInfo['invoiceApplyCategoryCode'];
        $invoice['currency'] = $invoice['basicInformation']['currency'];
        $invoice['contactName'] = $invoice['sellerDetails']['businessName'];
        $invoice['contactMobileNum'] = $this->taxpayer['MOBILE_PHONE'];
        $invoice['contactEmail'] = $invoice['sellerDetails']['emailAddress'];
        $invoice['source'] = strval(self::SOURCE_WEBSERVICE);
        $invoice['remarks'] = $this->genInfo['remarks'];
        $invoice['sellersReferenceNo'] = $this->genInfo['sellersReferenceNo'];

        unset($invoice['taxDetails']);
        $invoice['taxDetails'] = $this->getTaxDetails();

        unset($invoice['summary']);
        $invoice['summary'] = $this->getCreditNoteSummary();

        $invoice['goodsDetails'] = $this->createProductsList();

        unset($invoice['basicInformation']);
        unset($invoice['airlineGoodsDetails']);
        unset($invoice['buyerDetails']);
        unset($invoice['extend']);

        return $invoice;
    }

    /**
     * List of products and services to put in the credit note
     */
    public function createProductsList()
    {
        $invoice = $this->getEfrisInvoice();
        $localInvoiceDetails = $this->getLocalInvoiceDetails();
        Log::info(json_encode($invoice));
        $productList = [];

        foreach ($this->_productServices as $productService) {
            $index = $productService['orderNumber'];
            $product = $this->oriInvoiceGoodsDetails[$index];

            $prodservice = $productService;
            $goods = $localInvoiceDetails['goodsDetails'][$prodservice['OrderNumber'] ?? $prodservice['orderNumber']];
            echo 'Good or service'.$product['itemCode'], $goods; //invoice.goodsDetails)
            $item_rate = $prodservice['taxRule'] ?? 'URA';
            $taxDetails = KakasaProduct::getProductTaxCategory($prodservice['itemCode'], $invoice['basicInformation']['invoiceIndustryCode'], $item_rate);

            $good_or_service = [
                'item' => $goods['item'],
                'orderNumber' => $prodservice['OrderNumber'] ?? $prodservice['orderNumber'],
                'itemCode' => $goods['itemCode'],
                'qty' => $this->toNegative(strval($prodservice['quantity'])),
                'unitOfMeasure' => strval($goods['unitOfMeasure']),
                'unitPrice' => strval($goods['unitPrice']),
                'total' => $this->toNegative(strval($prodservice['quantity'] * $prodservice['unitPrice'])),
                'taxRate' => $taxDetails['taxRate'],
                'tax' => $this->toNegative(strval($this->getTaxAmount($prodservice['quantity'], $prodservice['unitPrice'], $taxDetails['taxRate']))),
                'deemedFlag' => $goods['deemedFlag'],
                'exciseFlag' => $goods['exciseFlag'],
                'goodsCategoryId' => $goods['goodsCategoryId'],
                'pack' => $goods['pack'],
            ];

            if ($goods['exciseFlag'] == '1') {
                $good_or_service['categoryId'] = $goods['categoryId'];
                $good_or_service['exciseRate'] = $goods['exciseRate'];
                $good_or_service['exciseRule'] = $goods['exciseRule'];
                $good_or_service['exciseTax'] = 0; // $this->toNegative($goods['exciseRate']);
                $good_or_service['exciseUnit'] = $goods['exciseUnit'];
                $good_or_service['exciseCurrency'] = $goods['exciseCurrency'];
                $good_or_service['exciseRateName'] = $goods['exciseRateName'];
            }

            $productList[] = $good_or_service;
        }

        return $productList;
    }

    /**
     * Tax Details for this Credit Note
     */
    public function getTaxDetails()
    {
        $invoice = $this->getEfrisInvoice();
        $itemsBought = [];

        foreach ($this->_productServices as $productService) {
            $product = array_filter($invoice['goodsDetails'], function ($x) use ($productService) {
                return $x['itemCode'] == $productService['itemCode'];
            });
            $product = reset($product);

            $prodservice = $productService;
            $item_rate = $prodservice['taxRule'] ?? 'URA';
            $taxDetails = KakasaProduct::getProductTaxCategory($prodservice['itemCode'], $invoice['basicInformation']['invoiceIndustryCode'], $item_rate);

            $itemsBought[] = [
                'taxCategory' => $taxDetails['taxCategory'],
                'taxCategoryCode' => $taxDetails['taxCategoryCode'],
                'netAmount' => $this->toNegative(strval($this->getNetAmount($prodservice['quantity'], $prodservice['unitPrice'], $taxDetails['taxRate']))),
                'taxRate' => $taxDetails['taxRate'],
                'taxAmount' => $this->toNegative(strval($this->getTaxAmount($prodservice['quantity'], $prodservice['unitPrice'], $taxDetails['taxRate']))),
                'grossAmount' => $this->toNegative(strval($this->getGrossAmount($prodservice['quantity'], $prodservice['unitPrice']))),
                'exciseCurrency' => $product['exciseCurrency'],
                'taxRateName' => $taxDetails['taxRateName'],
            ];
        }

        return $itemsBought;
    }

    /**
     * Create the Credit Note Summary section
     */
    public function getCreditNoteSummary()
    {
        $taxes = $this->getTaxDetails();
        $summary = [
            'netAmount' => number_format(array_sum(array_column($taxes, 'netAmount')), 2),
            'taxAmount' => number_format(array_sum(array_column($taxes, 'taxAmount')), 2),
            'grossAmount' => number_format(array_sum(array_column($taxes, 'grossAmount')), 2),
            'itemCount' => count($taxes),
            'modeCode' => '1', //Issuing (0)Offline, or (1)Online?
            'remarks' => $this->genInfo['remarks'],
            'qrCode' => '',
        ];

        return $summary;
    }

    public function getGrossAmount($quantity, $price)
    {
        return $quantity * $price;
    }

    public function getNetAmount($quantity, $price, $taxRate)
    {
        $net = ($quantity * $price) / ($taxRate + 1);

        return ($taxRate === '-') ? ($quantity * $price) : (float) number_format($net, 2);
    }

    public function getTaxAmount($quantity, $price, $taxRate)
    {
        $tax = ($quantity * $price) - $this->getNetAmount($quantity, $price, $taxRate);

        return ($taxRate === '-') ? (0) : (float) number_format($tax, 2);
    }

    /**
     * Convert a number to negative
     *
     * @param {*} input
     */
    public function toNegative($input)
    {
        return -abs($input);
    }

    /**
     * Find a product (good/service) already registered in EFRIS
     *
     * @param  string  $code
     * @return mixed
     */
    public function getEfrisProduct($code)
    {
        $products = Cache::get('products');

        $product = $products->first(function ($item) use ($code) {
            return $item['goodsName'] === $code;
        });

        if ($product) {
            return $product;
        } else {
            $product = $products->first(function ($item) use ($code) {
                return $item['goodsCode'] === $code;
            });

            if ($product) {
                return $product;
            } else {
                return [
                    'errorCode' => 200,
                    'errorMessage' => "Product/Service with code or name $code not found",
                ];
            }
        }
    }
}

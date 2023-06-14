<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mhassan654\Uraefrisapi\Helpers\ArrayHelper;
use Mhassan654\Uraefrisapi\Models\EfrisInvoice;
use Mhassan654\Uraefrisapi\Models\KakasaProduct;
use Illuminate\Database\Eloquent\Factories\HasFactory;


/**
 * Base class for all fiscalised documents
 * Invoices, Receipts, Credit notes, etc
 */
class KakasaDocument extends Model
{
    use HasFactory;

    /**
     * Items (goods/services) in this document
     *
     * @var array
     */
    protected $items = [];

    /**
     * Create an instance of a fiscal document
     *
     * @param array $itemsBought Goods/services in this fiscal document
     */
    public function __construct(array $itemsBought)
    {
        $this->items = $itemsBought;
    }

    /**
     * Items bought
     *
     * @return array
     */
    public function prepareProductDetails()
    {
        $itemsBought = [];
        foreach ($this->items as $index => $item) {
            $goods = new KakasaProduct($item);
            $goods->orderNumber = $index;
            $itemsBought[] = $goods;
        }
        return $itemsBought;
    }

    /**
     * How many items are we selling in this invoice?
     *
     * @return int
     */
    public function getTotalInvoiceProducts()
    {
        $products = $this->prepareProductDetails();
        return count($products);
    }

    /**
     * Details for the [payway] Section of the invoice
     *
     * @return array
     */
    public function getTaxDetails()
    {
        $itemsBought = [];
        foreach ($this->items as $item) {
            $goodorservice = new KakasaProduct($item);
            $itemsBought[] = [
                'taxCategory' => 'Standard',
                'netAmount' => ($goodorservice->unitPrice * $goodorservice->qty),
                'taxRate' => $goodorservice->taxRate,
                'taxAmount' => ($goodorservice->qty * $goodorservice->unitPrice * $goodorservice->taxRate),
                'grossAmount' => (($goodorservice->qty * $goodorservice->unitPrice * $goodorservice->taxRate) + ($goodorservice->unitPrice * $goodorservice->qty)),
                'total' => $this->getTotalPrice($goodorservice),
                'tax' => $goodorservice->tax,
                'discountFlag' => $goodorservice->discountFlag,
                'discountTotal' => $goodorservice->discountTotal,
                'taxForm' => $goodorservice->taxForm,
                'exciseRate' => $goodorservice->exciseRate,
                'exciseTax' => $goodorservice->exciseTax,
                'exciseUnit' => $goodorservice->exciseUnit,
                'exciseCurrency' => $goodorservice->exciseCurrency,
                'taxRateName' => 'VAT',
            ];
        }
        return $itemsBought;
    }

    /**
     * The total price for a good or service
     *
     * @param KakasaProduct $goodorservice
     * @return float
     */
    public function getTotalPrice(KakasaProduct $goodorservice)
    {
        $discountedAmount = 0;

        switch ($goodorservice->discountFlag) {
            case '0':
                $discountedAmount = $goodorservice->unitPrice;
                break;
            case '1':
                $discountedAmount = $goodorservice->discountTotal;
                break;
            default:
            case '2':
                $discountedAmount = 0;
                break;
        }

        return $goodorservice->unitPrice * $goodorservice->qty - $discountedAmount;
    }

    /**
     * Create the invoice Summary section
     *
     * @return array
     */
    public function getInvoiceSummary()
    {
        $taxes = $this->getTaxDetails();
        $summary = [
            'netAmount' => ArrayHelper::getArraySum($taxes, 'netAmount'),
            'taxAmount' => ArrayHelper::getArraySum($taxes, 'taxAmount'),
            'grossAmount' => ArrayHelper::getArraySum($taxes, 'grossAmount'),
            'itemCount' => $this->getTotalInvoiceProducts(),
            'modeCode' => '0', //Issuing (0)Offline, or (1)Online?
            'remarks' => '',
            'qrCode' => '',
        ];
        return $summary;
    }

    /**
     * Get details of an invoice issued earlier
     *
     * @return mixed
     */
    public function getInvoiceDetails()
    {
        $url = taxpayer::KUMUSOFT_MIDDLEWARE_URL . '/invoice-details/' . $this->generalInformation['invoice_no'];
        $response = Http::get($url);
        return $response->json();
    }

    /**
     * Find an invoice issued earlier via EFRIS
     *
     * @param int $fiscalNumber
     * @return mixed
     */
    public function getEfrisInvoice($fiscalNumber)
    {
        $invoice = EfrisInvoice::where('basicInformation.invoiceNo', $this->oriInvoiceNumber)->first();
        return $invoice;
    }

    /**
     * Generate a QR code as a base64 String from a given Text
     *
     * @param string $qrText
     * @return string
     */
    public static function generateQrCode($qrText)
    {
        $qR64Base = QRCode::toBase64($qrText);
        return $qR64Base;
    }

    /**
     * Generate the QrCode in a picture format
     *
     * @param string $base64Text
     * @param string $invoiceNo
     * @return bool
     */
    // public static function generateQrCodeImage($base64Text, $invoiceNo)
    // {
    //     $base64Data = str_replace('data:image/png;base64,', '', $base64Text);
    //     $dir = storage_path('app/qrcode');

    //     if (!file_exists($dir)) {
    //         mkdir($dir, 0755, true);
    //     }

    //     $filePath = $dir . '/' . $invoiceNo . '.png';

    //     if (file_exists($filePath)) {
    //         return true;
    //     } else {
    //         file_put_contents($filePath, base64_decode($base64Data));
    //     }
    // }
}

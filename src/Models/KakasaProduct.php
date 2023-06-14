<?php

namespace Mhassan654\Uraefrisapi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;


/**
 * A good or service which has been registered with the
 * EFRIS system
 */
class KakasaProduct extends Model
{
    public $item = '';
    public $itemCode = '';
    public $qty = '';
    public $unitOfMeasure = '';
    public $unitPrice = '';
    public $total = 0;
    public $taxRate = 0;
    public $tax = 0;
    public $orderNumber = '';
    public $deemedFlag = '2';
    public $exciseFlag = '2';
    public $categoryId = '';
    public $categoryName = '';
    public $goodsCategoryId = 0;
    public $goodsCategoryName = '';
    public $exciseRate = null;
    public $exciseRule = '2';
    public $exciseTax = 0;
    public $pack = 0;
    public $stick = '';
    public $exciseUnit = '';
    public $exciseCurrency = 101;
    public $exciseRateName = '';
    public $discountTaxRate = 0;
    public $product = [];
    public $isZeroRate = '';
    public $isExempt = '';
    public $taxCategory = '';
    public $taxRateName = '';
    public $taxForm = '';
    public $discountFlag = '';
    public $discountTotal = 0;

    public function __construct($product, $invoiceIndustry = '101')
    {
        $this->product = $product;
        $this->invoiceIndustryCode = $invoiceIndustry;
        $this->init();
    }

    /**
     * Assign values from whats already known
     */

    public function init()
    {
        $response = self::getEfrisProduct( $this->product->itemCode);
        $goodorservice = $response->json();

        if ($goodorservice['errorCode'] === 404) {
            return $goodorservice;
        } else {
            $this->item = $goodorservice['goodsName'];
            $this->itemCode = $goodorservice['goodsCode'];
            $this->goodsCategoryId = $goodorservice['commodityCategoryCode'];
            $this->goodsCategoryName = $goodorservice['commodityCategoryName'];
            $this->taxRate = $this->product->taxRate;
            $this->unitOfMeasure = $goodorservice['measureUnit'];
            $this->unitPrice = $this->product->unitPrice;
            $this->qty = $this->product->quantity;
            $this->total = number_format($this->unitPrice * $this->qty, 3);
            $this->isZeroRate = ($this->product->taxRateName === 'STANDARD' || $this->product->taxRule === 'STANDARD') ? '102' : $goodorservice['isZeroRate'];
            $this->isExempt = ($this->product->taxRateName === 'STANDARD' || $this->product->taxRule === 'STANDARD') ? '102' : $goodorservice['isExempt'];
            $this->taxCategory = $this->product->taxCategory;
            $this->taxRateName = $this->product->taxRateName;
            $this->taxForm = $this->product->taxForm;
            $this->orderNumber = 0;
            $this->exciseFlag = $this->product->exciseFlag;

            if ($this->discountFlag === '2') {
                $this->discountTaxRate = $this->getDiscountTaxRate();
                $this->discountTotal = $this->product->discountTotal;
            }

            $this->deemedFlag = $this->product->deemedFlag ?: '2';
            $this->item = ($this->deemedFlag === '1') ? $this->item . ' (Deemed)' : $this->item;

            $this->exciseCurrency = $this->product->exciseCurrency;
            $this->netAmount = $this->getNetAmount();

            if ($this->isExempt === '102' || $this->product->isZeroRate === '102') {
                $this->tax = $this->getTaxAmount();
            }

            $this->grossAmount = $this->netAmount + $this->tax;
            $this->exciseRule = 1;

            if ($this->exciseFlag === '1' || $this->exciseFlag === '101') {
                $this->exciseRateName = $goodorservice['exciseRate'] ? $goodorservice['exciseRate'] * 100 . '%' : $goodorservice['exciseRate'];
                $this->exciseTax = number_format($this->product->exciseTax, 2);
                $this->exciseUnit = $goodorservice['exciseUnit'] ?: $this->product->exciseUnit;
                $this->exciseRate = $this->product->exciseRate;
                $this->categoryId = $goodorservice['exciseDutyCode'];
                $this->categoryName = $goodorservice['exciseDutyName'];
                $this->exciseRule = $this->product->exciseRule ?: '1';
                $this->stick = $goodorservice['stick'];
                $this->pack = $goodorservice['pack'];
            }

            if ($this->discountFlag === '1') {
                $this->discountLine = $this->initDiscount();
            }

            $this->hasErrors = 0;
            unset($this->product);

            return $this;
        }
    }

    /**
     * Create the discount line for this product
     */
    public function initDiscount(): array
    {
        $response = Http::get('your-api-endpoint/' . $this->product->itemCode);
        $goodorservice = $response->json();

        $total_price = $this->product->total;

        $discountLine = [
            'deemedFlag' => $this->product->deemedFlag ?: '2',
            'discountFlag' => 0,
            'discountTaxRate' => $this->getDiscountTaxRate(),
            'goodsCategoryId' => $goodorservice['commodityCategoryCode'],
            'categoryId' => $goodorservice['exciseDutyCode'] ?? '',
            'goodsCategoryName' => $goodorservice['commodityCategoryName'],
            'item' => $goodorservice['goodsName'] . ' (Discount)',
            'itemCode' => $goodorservice['goodsCode'],
            'taxRate' => $this->getDiscountTaxRate(),
            'total' => -abs($this->product->discountTotal),
            'unitOfMeasure' => $goodorservice['measureUnit'],
            'exciseFlag' => $this->product->exciseFlag,
        ];

        if ($this->product->exciseFlag === '1') {
            $discountLine['exciseRate'] = $this->product->exciseRate;
            $discountLine['exciseTax'] = ($this->product->exciseRule === '2') ? 0 : -abs(($this->product->discountTotal / $this->total) * $this->product->exciseTax);
            $discountLine['pack'] = $goodorservice['pack'];
            $discountLine['stick'] = $goodorservice['stick'];
            $discountLine['exciseCurrency'] = $this->product->exciseCurrency;
            $discountLine['exciseRule'] = $this->product->exciseRule;
        }

        return $discountLine;
    }

    public function getProductDetails($path, $attribs)
    {
        try {
            $response = Http::post($path, $attribs);
            $result = $response->json();
            return $result;
        } catch (\Exception $error) {
            dd($error);
        }
    }

    public function getDiscountTaxRate(): string
    {
        $discountTaxRate = 0;

        if ($this->taxRate > 0) {
            $discountTaxRate = $this->product->discountTotal / $this->product->total;
        }

        return number_format($discountTaxRate, 2);
    }

    public function getEfrisProduct($code)
    {
        $products = \Illuminate\Support\Facades\Cache::get("products");

        $product = $products->first(function ($item) use ($code) {
            return $item->goodsName === $code;
        });

        if ($product) {
            return $product;
        } else {
            $product = $products->first(function ($item) use ($code) {
                return $item->goodsCode === $code;
            });

            if ($product) {
                return $product;
            } else {
                return [
                    "errorCode" => 200,
                    "errorMessage" => "Product/Service with code or name {$code} not found"
                ];
            }
        }
    }

    /**
     * Method to get product details
     * @param string $code
     * @return mixed
     */
    public static function getEfrisProductDetails($code)
    {
        $products = store()->get("products");

        $product = collect($products)->first(function ($item) use ($code) {
            return $item['goodsName'] === $code;
        });

        if ($product) {
            return $product;
        } else {
            $product = collect($products)->first(function ($item) use ($code) {
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

    /**
     * List of products for making T139 Requests
     * @param array $products
     * @return array
     */
    public static function prepareT139ProductList($products): array
    {
        $stockInItems = [];
        foreach ($products as $stockItem) {
            $_product = self::getEfrisProductDetails($stockItem['itemCode'] ?? $stockItem['itemName']);
            $stockItem['commodityGoodsId'] = $_product['id'] ?? null;
            $stockItem['goodsCode'] = $_product['goodsCode'] ?? null;
            $stockItem['measureUnit'] = $_product['measureUnit'] ?? null;
            unset($stockItem['itemCode']);
            $stockInItems[] = $stockItem;
        }

        return $stockInItems;
    }

    /**
     * List of products for making T131 Requests
     * @param array $products
     * @return array
     */
    public static function prepareT131ProductList($products): array
    {
        $stockInItems = [];
        foreach ($products as $stockItem) {
            $_product = self::getEfrisProductDetails($stockItem['itemCode'] ?? $stockItem['itemName']);
            if (isset($_product['errorCode'])) {
                return $_product;
            }
            $stockItem['commodityGoodsId'] = $_product['id'] ?? null;
            $stockItem['goodsCode'] = $_product['goodsCode'] ?? null;
            unset($stockItem['itemCode']);
            $stockInItems[] = $stockItem;
        }

        return $stockInItems;
    }

    /**
     * Update an object (if it exists), or add a new one
     * @param array $product
     * @return mixed
     */
    public static function upsertProductDetails($product)
    {
        $filter = [
            'goodsCode' => $product['goodsCode'],
        ];

        return EfrisProduct::updateOrCreate($filter, $product);
    }

    /**
     * Save multiple products
     * @param array $products
     * @return mixed
     */
    public static function saveManyProducts($products)
    {
        return EfrisProduct::insert($products);
    }

    /**
     * Get the tax amount
     * Tax = TotalPrice - (Net Amount)
     * @return float
     */
    public function getTaxAmount(): float
    {
        $taxAmount = $this->product['total'] - $this->getNetAmount();
        return round($taxAmount, 2);
    }

    /**
     * Get the net amount of the product
     * =(TotalPrice*100)/(100+TaxRate)
     * @return float
     */
    public function getNetAmount(): float
    {
        $itemRate = $this->product['taxRule'] ?? $this->product['taxRateName'] ?? "URA";
        $productTax = isset($this->taxRateName) ? (float)$this->taxRateName : 0;

        $netPrice = $this->product['total'] / ($productTax + 1);
        $netAmount = 0;

        if ($itemRate == "STANDARD" || $itemRate == "URA") {
            $netAmount = round($netPrice, 2);
        } else {
            $netAmount = ($this->invoiceIndustryCode == "102" ||
                $this->isExempt == "101" ||
                $itemRate == "EXEMPT" ||
                $itemRate == "EXEMPTED" ||
                $itemRate == "ZERORATED" ||
                $this->isZeroRate == "101") ? (float)$this->product['total'] : (float)$netPrice;
        }

        // Remove discount if any
        return ($this->discountFlag == "2") ? round($netAmount, 2) : round(($netAmount + (float)$this->discountTotal), 2);
    }

    /**
     * Get the product's tax rate
     * @return float
     */
    public function getProductTaxRate()
    {
        $item_rate = $this->product->taxRule ?? $this->taxRateName ?? "URA";
        // Depending on the industry code...
        switch ($this->invoiceIndustryCode) {
            case "102": // Exports
            case 102:
                $this->taxCategory = "Zero-rated";
                $this->taxRateName = "0%";
                $this->tax = 0;
                $this->taxRate = 0;
                $this->taxCategoryCode = "02";
                break;

            case "101": // General Industry
            case "103": // Import
            case "104": // Imported Service
            case "105": // Telecom
            case "106": // Stamp Duty
            case "107": // Hotel Service
            case "108": // Other Taxes
            default:
                if ($item_rate == "STANDARD") {
                    $this->taxRateName = "18%";
                    $this->taxCategory = "Standard";
                    $this->taxRate = 0.18;
                    $this->taxCategoryCode = "01";
                } elseif ($this->deemedFlag == "1" || $item_rate == "DEEMED") {
                    $this->taxCategory = "Deemed";
                    $this->taxRateName = "18%";
                    $this->taxRate = 0.18;
                    $this->taxCategoryCode = "04";
                } elseif ($this->isExempt == "101" || $this->isExempt === 101 || $item_rate == "EXEMPT" || $item_rate == "EXEMPTED") {
                    $this->taxCategory = "Exempt";
                    $this->taxRateName = "-";
                    $this->tax = 0;
                    $this->taxRate = "-";
                    $this->taxCategoryCode = "03";
                } elseif ($this->isZeroRate == "101" || $this->isZeroRate == 101 || $item_rate == "ZERORATED") {
                    $this->taxCategory = "Zero-rated";
                    $this->taxRateName = "0%";
                    $this->tax = 0;
                    $this->taxRate = 0;
                    $this->taxCategoryCode = "02";
                } else {
                    $this->taxRateName = "18%";
                    $this->taxCategory = "Standard";
                    $this->taxRate = 0.18;
                    $this->taxCategoryCode = "01";
                }
                break;
        }
        return $this->taxRate;
    }

    public static function getProductTaxCategory($productCode, $industryCode = '101', $itemTaxRule = 'URA'): array
    {
        $product = KakasaProduct::getEfrisProductDetails($productCode);

        if ($product) {
            $taxProperties = [];

            // TaxRule?
            $taxRule = ($itemTaxRule == "URA") ? KakasaProduct::getProductTaxRule($industryCode, $product->isZeroRate, $product->isExempt) : $itemTaxRule;

            // Depending on the taxRule...
            switch ($taxRule) {
                case "ZERORATED": // Zero-rated proucts/services
                    $taxProperties['taxCategory'] = "Zero-rated";
                    $taxProperties['taxRateName'] = "0%";
                    $taxProperties['taxRate'] = 0;
                    $taxProperties['taxCategoryCode'] = "02";
                    break;
                case "EXEMPTED": // Exempt Products/Services
                case "EXEMPT":
                    $taxProperties['taxCategory'] = "Exempt";
                    $taxProperties['taxRateName'] = "-";
                    $taxProperties['taxRate'] = "-";
                    $taxProperties['taxCategoryCode'] = "03";
                    break;
                case "STANDARD": // Standard Rated
                default:
                    $taxProperties['taxRateName'] = "18%";
                    $taxProperties['taxCategory'] = "Standard";
                    $taxProperties['taxRate'] = 0.18;
                    $taxProperties['taxCategoryCode'] = "01";
                    break;
            }

            return $taxProperties;
        } else {
            return [
                "error" => "NOT_FOUND",
                "message" => "Item with code {$productCode} not found"
            ];
        }
    }

    public static function getProductTaxRule($industryCode, $isZeroRate, $isExempt = 102): string
    {
        if (intval($industryCode) === 102 || intval($isZeroRate) === 101) {
            return "ZERORATED";
        } elseif (intval($isExempt) === 101) {
            return "EXEMPT";
        } else {
            return "STANDARD";
        }
    }



}
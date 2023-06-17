<?php

namespace Mhassan654\Uraefrisapi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class KumusoftKakasa extends Model
{
    /**
     * The Current date and time
     *
     * @return string
     */
    public static function currentDateAndTime()
    {
        return now()->format('Y-m-d H:i:s');
    }

    /**
     * Prepare request data
     *
     * @param  string  $content
     * @param  string  $interface_code
     * @param  bool  $is_encrypted
     * @return array
     */
    public static function prepareRequestData($content, $interface_code)
    {
        $reqTime = self::currentDateAndTime();

        return [
            'data' => [
                'content' => $content === '' ? null : self::base64Encode(json_encode($content)),
                'signature' => '',
                'dataDescription' => [
                    'codeType' => '0',
                    'encryptCode' => '1',
                    'zipCode' => '0',
                ],
            ],
            'globalInfo' => [
                'appId' => config('uraefrisapi.taxpayer.APP_ID'),
                'brn' => config('uraefrisapi.taxpayer.BRN'),
                'dataExchangeId' => config('uraefrisapi.taxpayer.DATA_EXCHANGE_ID'),
                'deviceMAC' => config('uraefrisapi.taxpayer.DEVICE_MAC'),
                'deviceNo' => config('uraefrisapi.taxpayer.DEVICE_NO'),
                'extendField' => null,
                'longitude' => config('uraefrisapi.taxpayer.LONGITUDE'),
                'latitude' => config('uraefrisapi.taxpayer.LATITUDE'),
                'interfaceCode' => $interface_code,
                'requestCode' => config('uraefrisapi.taxpayer.REQUEST_CODE'),
                'requestTime' => $content['stockInDate'] != null ? $content['stockInDate'] : self::currentDateAndTime(),
                'responseCode' => config('uraefrisapi.taxpayer.RESPONSE_CODE'),
                'taxpayerID' => config('uraefrisapi.taxpayer.TAXPAYER_ID'),
                'tin' => config('uraefrisapi.taxpayer.TIN'),
                'userName' => config('uraefrisapi.taxpayer.USERNAME'),
                'version' => config('uraefrisapi.taxpayer.VERSION'),
            ],
            'returnStateInfo' => [
                'returnCode' => '',
                'returnMessage' => '',
            ],
        ];
    }

    /**
     * Prepare increase request data
     *
     * @param  string  $content
     * @param  string  $interface_code
     * @return array
     */
    public static function prepareIncreaseRequestData($content, $interface_code)
    {
        return [
            'data' => [
                'content' => $content === '' ? null : self::base64Encode(json_encode($content)),
                'signature' => '',
                'dataDescription' => [
                    'codeType' => '0',
                    'encryptCode' => '1',
                    'zipCode' => '0',
                ],
            ],
            'globalInfo' => [
                'appId' => config('uraefrisapi.taxpayer.APP_ID'),
                'brn' => config('uraefrisapi.taxpayer.BRN'),
                'dataExchangeId' => config('uraefrisapi.taxpayer.DATA_EXCHANGE_ID'),
                'deviceMAC' => config('uraefrisapi.taxpayer.DEVICE_MAC'),
                'deviceNo' => config('uraefrisapi.taxpayer.DEVICE_NO'),
                'extendField' => null,
                'longitude' => config('uraefrisapi.taxpayer.LONGITUDE'),
                'latitude' => config('uraefrisapi.taxpayer.LATITUDE'),
                'interfaceCode' => $interface_code,
                'requestCode' => config('uraefrisapi.taxpayer.REQUEST_CODE'),
                'requestTime' => $content['stockInDate'] != null ? $content['stockInDate'] : self::currentDateAndTime(),
                'responseCode' => config('uraefrisapi.taxpayer.RESPONSE_CODE'),
                'taxpayerID' => config('uraefrisapi.taxpayer.TAXPAYER_ID'),
                'tin' => config('uraefrisapi.taxpayer.TIN'),
                'userName' => config('uraefrisapi.taxpayer.USERNAME'),
                'version' => config('uraefrisapi.taxpayer.VERSION'),
            ],
            'returnStateInfo' => [
                'returnCode' => '',
                'returnMessage' => '',
            ],
        ];
    }

    /**
     * Convert a Base64 encoded string into a normal string
     *
     * @param  string  $content
     * @param  string  $isZipped
     * @return string
     */
    public static function base64Decode($content, $isZipped = '0')
    {
        if (intval($isZipped) === 1) {
            $buffer = base64_decode($content);
            $result =Zlib::unzip($buffer);

            return $result;
        } else {
            return base64_decode($content);
        }
    }

    /**
     * Convert a normal string (JSON) to a base64 encoded string
     *
     * @param  string  $content
     * @return string
     */
    public static function base64Encode($content)
    {
        return base64_encode($content);
    }

    /**
     * Prepare items to be registered with URA
     *
     * @param  array  $item
     * @return array
     */
    public static function prepareInventoryData($item)
    {
        $items = [];
        foreach ($item as $i) {
            $items[] = [
                'operationType' => $i['operationType'] ?? '101',
                'goodsName' => $i['goodsName'],
                'goodsCode' => $i['goodsCode'],
                'measureUnit' => $i['measureUnit'],
                'unitPrice' => $i['unitPrice'],
                'currency' => $i['currency'],
                'commodityCategoryId' => $i['commodityCategoryId'],
                'haveExciseTax' => $i['haveExciseTax'],
                'description' => $i['description'],
                'stockPrewarning' => $i['stockPrewarning'],
                'pieceMeasureUnit' => $i['pieceMeasureUnit'],
                'havePieceUnit' => $i['havePieceUnit'],
                'pieceUnitPrice' => $i['pieceUnitPrice'],
                'packageScaledValue' => $i['packageScaledValue'],
                'pieceScaledValue' => $i['pieceScaledValue'],
                'exciseDutyCode' => $i['exciseDutyCode'],
                'haveOtherUnit' => ! isset($i['haveOtherUnit']) || $i['havePieceUnit'] === '102' ? '102' : $i['haveOtherUnit'],
                'goodsOtherUnits' => $i['haveOtherUnit'] === '101' ? $i['goodsOtherUnits'] : [],
            ];
        }

        return $items;
    }

    /**
     * Get the current Unix timestamp
     *
     * @return int
     */
    public static function unixTimeStamp()
    {
        return now()->timestamp;
    }

    /**
     * Prepare invoice inquiry data
     *
     * @param  mixed  $data
     * @return mixed
     */
    public static function prepareInvoiceInquiryData($data)
    {
        $invoiceData = new KakasaInvoice($data);

        return $invoiceData->prepareInvoiceDetails($data);
    }

    /**
     * Prepare details of a credit note
     *
     * @param  mixed  $basicInfo
     * @param  mixed  $itemsBought
     * @return mixed
     */
    public static function prepareCreditNoteInfo($basicInfo, $itemsBought)
    {
        $fiscalNumber = $basicInfo['invoice_no'];
        $originalInvoice = KakasaInvoice::getEfrisInvoice($fiscalNumber);

        return $originalInvoice;
    }

    /**
     * Call a GET API
     *
     * @param  string  $path
     * @return mixed
     */
    public static function callGetAPI($path)
    {
        try {
            $response = Http::get($path);
            $result = $response->json();

            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Call a POST API
     *
     * @param  string  $path
     * @param  mixed  $attribs
     * @return mixed
     */
    public static function callPostAPI($path, $attribs)
    {
        try {
            $response = Http::post($path, $attribs);
            $result = $response->json();

            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}

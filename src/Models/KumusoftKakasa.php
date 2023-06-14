<?php

namespace Mhassan654\Uraefrisapi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * @param string $content
     * @param string $interface_code
     * @param bool $is_encrypted
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
                'appId' => Taxpayer::APP_ID,
                'brn' => Taxpayer::BRN,
                'dataExchangeId' => Taxpayer::DATA_EXCHANGE_ID,
                'deviceMAC' => Taxpayer::DEVICE_MAC,
                'deviceNo' => Taxpayer::DEVICE_NO,
                'extendField' => null,
                'longitude' => Taxpayer::LONGITUDE,
                'latitude' => Taxpayer::LATITUDE,
                'interfaceCode' => $interface_code,
                'requestCode' => Taxpayer::REQUEST_CODE,
                'requestTime' => self::currentDateAndTime(),
                'responseCode' => Taxpayer::RESPONSE_CODE,
                'taxpayerID' => Taxpayer::TAXPAYER_ID,
                'tin' => Taxpayer::TIN,
                'userName' => Taxpayer::USERNAME,
                'version' => Taxpayer::VERSION,
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
     * @param string $content
     * @param string $interface_code
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
                'appId' => Taxpayer::APP_ID,
                'brn' => Taxpayer::BRN,
                'dataExchangeId' => Taxpayer::DATA_EXCHANGE_ID,
                'deviceMAC' => Taxpayer::DEVICE_MAC,
                'deviceNo' => Taxpayer::DEVICE_NO,
                'extendField' => null,
                'longitude' => Taxpayer::LONGITUDE,
                'latitude' => Taxpayer::LATITUDE,
                'interfaceCode' => $interface_code,
                'requestCode' => Taxpayer::REQUEST_CODE,
                'requestTime' => $content['stockInDate'] != null ? $content['stockInDate'] : self::currentDateAndTime(),
                'responseCode' => Taxpayer::RESPONSE_CODE,
                'taxpayerID' => Taxpayer::TAXPAYER_ID,
                'tin' => Taxpayer::TIN,
                'userName' => Taxpayer::USERNAME,
                'version' => Taxpayer::VERSION,
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
     * @param string $content
     * @param string $isZipped
     * @return string
     */
    public static function base64Decode($content, $isZipped = '0')
    {
        if (intval($isZipped) === 1) {
            $buffer = base64_decode($content);
            $result = Zlib::unzip($buffer);
            return $result;
        } else {
            return base64_decode($content);
        }
    }

    /**
     * Convert a normal string (JSON) to a base64 encoded string
     *
     * @param string $content
     * @return string
     */
    public static function base64Encode($content)
    {
        return base64_encode($content);
    }

    /**
     * Prepare items to be registered with URA
     *
     * @param array $item
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
                'haveOtherUnit' => !isset($i['haveOtherUnit']) || $i['havePieceUnit'] === '102' ? '102' : $i['haveOtherUnit'],
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
     * @param mixed $data
     * @return mixed
     */
    public static function prepareInvoiceInquiryData($data)
    {
        $invoiceData = new KakasaInvoice($data, []);
        return $invoiceData->prepareInvoiceDetails($data);
    }

    /**
     * Prepare details of a credit note
     *
     * @param mixed $basicInfo
     * @param mixed $itemsBought
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
     * @param string $path
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
     * @param string $path
     * @param mixed $attribs
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


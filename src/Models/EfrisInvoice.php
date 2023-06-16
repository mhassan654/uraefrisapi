<?php

namespace Mhassan654\Uraefrisapi\Models;

class EfrisInvoice
{
    use HasFactory;

    protected $fillable = [
        'basicInformation',
        'buyerDetails',
        'extend',
        'goodsDetails',
        'payWay',
        'sellerDetails',
        'summary',
        'taxDetails',
    ];

    protected $casts = [
        'basicInformation' => 'array',
        'buyerDetails' => 'array',
        'extend' => 'array',
        'goodsDetails' => 'array',
        'payWay' => 'array',
        'sellerDetails' => 'array',
        'summary' => 'array',
        'taxDetails' => 'array',
    ];
}

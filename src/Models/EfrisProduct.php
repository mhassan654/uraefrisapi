<?php

namespace Mhassan654\Uraefrisapi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EfrisProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'commodityCategoryCode',
        'commodityCategoryName',
        'createDate',
        'currency',
        'dateFormat',
        'exclusion',
        'goodsCode',
        'goodsName',
        'haveExciseTax',
        'havePieceUnit',
        'id',
        'isExempt',
        'isZeroRate',
        'measureUnit',
        'nowTime',
        'pageIndex',
        'pageNo',
        'pageSize',
        'remarks',
        'source',
        'statusCode',
        'stock',
        'stockPrewarning',
        'taxRate',
        'timeFormat',
        'unitPrice',
    ];
}

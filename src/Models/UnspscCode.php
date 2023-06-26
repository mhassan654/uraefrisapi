<?php

namespace Mhassan654\Uraefrisapi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnspscCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'segment',
        'segmentName',
        'family',
        'familyName',
        'class',
        'className',
        'commodity',
        'commodityName',
        'exciseDutyProductType',
        'vatRate',
        'serviceMark',
        'zeroRate',
        'zeroRateBegingTime',
        'exempt',
        'exemptBegingTime',
        'exclusion',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnspsCode extends Model
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

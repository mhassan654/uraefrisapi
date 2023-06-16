<?php

namespace Mhassan654\Uraefrisapi\Helpers;

class ArrayHelper
{
    /**
     * The sum of an array column
     *
     * @param  array  $theArray
     * @param  string  $theColumn
     * @return float
     */
    public static function getArraySum($theArray, $theColumn)
    {
        $sum = 0;
        foreach ($theArray as $item) {
            $sum += $item[$theColumn];
        }

        return round((float) $sum, 2);
    }
}

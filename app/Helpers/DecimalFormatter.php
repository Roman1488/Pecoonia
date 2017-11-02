<?php

namespace App\Helpers;

use App;

class DecimalFormatter
{
    public static function format($commaSeparatorVal, $v, $fractionSize = 2) {

        $decPoint = ".";
        $thousandsSep = ",";

        if ($commaSeparatorVal == 0)
        {
            $decPoint = ",";
            $thousandsSep = ".";
        }

        return (number_format($v, $fractionSize, $decPoint, $thousandsSep));
    }
}
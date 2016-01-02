<?php

namespace Ps2alerts\Api\Helper;

class DataFormatterHelper
{
    /**
     * Returns an array of dates based off two provided dates
     *
     * @param  string $dateFrom Date to start from
     * @param  string $dateTo   Date to finish on
     *
     * @return array
     */
    public function createDateRangeArray($dateFrom, $dateTo)
    {
        $dates = [];

        $dateFrom = mktime(
            1,
            0,
            0,
            substr($dateFrom, 5, 2),
            substr($dateFrom, 8, 2),
            substr($dateFrom, 0, 4)
        );
        $dateTo = mktime(
            1,
            0,
            0,
            substr($dateTo, 5, 2),
            substr($dateTo, 8, 2),
            substr($dateTo, 0, 4)
        );

        if ($dateTo >= $dateFrom) {
            array_push($dates, date('Y-m-d', $dateFrom)); // first entry
            while ($dateFrom < $dateTo) {
                $dateFrom += 86400; // add 24 hours
                array_push($dates, date('Y-m-d', $dateFrom));
            }
        }
        return $dates;
    }
}

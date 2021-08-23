<?php


namespace App\Helpers\General;


class Calendar
{
    const SUNDAY = 0;
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;

    public static function daysOfWeek()
    {
        return [
            Calendar::MONDAY    => __('Monday'),
            Calendar::TUESDAY   => __('Tuesday'),
            Calendar::WEDNESDAY => __('Wednesday'),
            Calendar::THURSDAY  => __('Thursday'),
            Calendar::FRIDAY    => __('Friday'),
            Calendar::SATURDAY  => __('Saturday'),
            Calendar::SUNDAY    => __('Sunday'),
        ];
    }
}
<?php

namespace MGModule\ResellersCenter\mgLibs\Helpers;

use DateTime;
use DateTimeZone;

class DateFormatter
{
    const DATE_FORMATS = [
        0=>'DD/MM/YYYY',
        1=>'DD.MM.YYYY',
        2=>'DD-MM-YYYY',
        3=>'MM/DD/YYYY',
        4=>'YYYY/MM/DD',
        5=>'YYYY-MM-DD'
    ];

    const DATE_FORMATS_DATE_TIME = [
        self::DATE_FORMATS[0]=>'d/m/Y',
        self::DATE_FORMATS[1]=>'d.m.Y',
        self::DATE_FORMATS[2]=>'d-m-Y',
        self::DATE_FORMATS[3]=>'m/d/Y',
        self::DATE_FORMATS[4]=>'Y/m/d',
        self::DATE_FORMATS[5]=>'Y-m-d'
    ];

    const ZERO_DATE = '0000-00-00';
    const ZERO_DATE_EXT = '0000-00-00 00:00:00';
    const TIME_FORMAT = 'H:i:s';

    protected $dateTime;

    public function __construct()
    {
        $this->dateTime = new DateTime();
    }

    public static function getDateFormats():array
    {
        return self::DATE_FORMATS;
    }

    public static function getGlobalFormat():string
    {
        global $CONFIG;
        return $CONFIG['DateFormat'];
    }

    public function format($date, $format = null, $withTime = false):string
    {
        $format = $format ??  self::getGlobalFormat();
        $dateTimeFormat = $withTime ? self::DATE_FORMATS_DATE_TIME[$format]. ' '.self::TIME_FORMAT : self::DATE_FORMATS_DATE_TIME[$format];

        if ($date != self::ZERO_DATE && $date != self::ZERO_DATE_EXT) {
            $dateTime = $date instanceof DateTime ? $date : new DateTime($date);
            $date = $dateTime->format($dateTimeFormat);
        } else {
            $dateTime = new DateTime('NOW');
            $date = $dateTime->format($dateTimeFormat);
            $date = preg_replace('/\d/', '0', $date);
        }
        return $date;
    }

}
<?php

namespace Services;

/*
Create a Web API that can be used to:
1. Find out the number of days between two datetime parameters.
2. Find out the number of weekdays between two datetime parameters.
3. Find out the number of complete weeks between two DateTime parameters.
4. Accept a third parameter to convert the result of (1, 2 or 3) into one of seconds,
minutes, hours, years.
5. Allow the specification of a timezone for comparison of input parameters from different
time zones.
*/

use Data\PublicHoliday;
use DateTime;
use Exception;

class DateTimeService
{

    const STATE_SA = 'sa';
    const STATE_NSW = 'nsw';
    const STATE_VIC = 'vic';

    const VALID_STATES = [
        self::STATE_SA,
        self::STATE_NSW,
        self::STATE_VIC
    ];

    public $lazyLoadedPublicHolidays;

    /**
     * I am in a habit of making all my functions public as I unit test every function individually and mock out any
     * sub functions the function I am testing calls
     * @param $state
     * @param $from
     * @param $to
     * @param bool $skipWeekends
     * @param bool $skipPublicHolidays
     * @return int
     * @throws Exception
     */
    public function getNumberOfWorkingDaysBetween($state, $from, $to, bool $skipWeekends = true, bool $skipPublicHolidays = true): int
    {
        // could also throw an exception
        if (!in_array($state, self::VALID_STATES)) {
            throw new Exception("State must be one of " . implode(", ", self::VALID_STATES));
        }
        /**
         * Great validation library
         * https://laravel.com/docs/10.x/validation#available-validation-rules
         */
        $fromDateTime = new DateTime($from);
        $toDateTime = new DateTime($to);

        $days = 0;
        while ($fromDateTime < $toDateTime) {
            if ($skipPublicHolidays && !$this->isDateOnAPublicHoliday($state, $fromDateTime)) {
                continue;
            }
            if ($skipWeekends && !$this->isDateOnAWeekend($state)) {
                continue;
            }
            $fromDateTime->modify('+1 day');
            $days++;
        }
        return $days;
    }

    public function isDateOnAWeekend(DateTime $dateTime)
    {
        return in_array($dateTime->format('l'), ["Saturday", "Sunday"]);
    }

    public function isDateOnAPublicHoliday($state, DateTime $dateTime)
    {
        // hard coding to South Australia
        return in_array($dateTime->format("Y-m-d"), $this->getPublicHolidaysAsYMDArray($state));
    }

    /**
     * @param $state
     * @return array
     */
    public function getPublicHolidaysAsYMDArray($state): array
    {
        // I am validating this above however this function may be called from other locations in the future
        if (empty($state) || !in_array($state, self::VALID_STATES)) {
            return [];
        }

        // I often use the variable name $toReturn when it's clear what it's purpose is
        $toReturn = [];
        foreach ($this->fetchPublicHolidays() as $publicHoliday) {
            $toReturn[] = $publicHoliday->dateYMD;
        }
        return $toReturn;
    }

    /**
     * Could make it strict, I have worked in legacy codebases so have found on real use for it in most cases:
     * public function fetchPublicHolidays(): PublicHoliday[]
     *
     * @return PublicHoliday[]
     */
    public function fetchPublicHolidays(): array
    {
        if ($this->lazyLoadedPublicHolidays) {
            return $this->lazyLoadedPublicHolidays;
        }

        // public holiday data from http://data.gov.au
        $publicHolidaysArray = json_decode(file_get_contents('example_public_holidays.json'), true);

        $publicHolidays = [];
        foreach ($publicHolidaysArray as $array) {
            $publicHolidayObject = $this->getPublicHolidayFromJSONArray($array);
            if ($publicHolidayObject) {
                $publicHolidays[] = $publicHolidayObject;
            }
        }

        return $this->lazyLoadedPublicHolidays = $publicHolidays;
    }

    public function getPublicHolidayFromJSONArray($array): ?PublicHoliday
    {
        // the only piece of information actually needed is Date, the Holiday Name and Jurisdiction are just nice to have
        if (isset($array["Date"])) {
            return null;
        }
        $publicHoliday = new PublicHoliday();

        // cannot trust the JSON to contain the properties we need, thus the ??
        $publicHoliday->name = $array["Holiday Name"] ?? "";
        $publicHoliday->dateYMD = $array["Date"];
        $publicHoliday->state = $array["Jurisdiction"] ?? "";
        return $publicHoliday;
    }

}

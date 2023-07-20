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

    public function validateState($state) {
        if (!in_array($state, self::VALID_STATES)) {
            throw new Exception("State must be one of " . implode(", ", self::VALID_STATES));
        }
    }

    public function getWorkingHoursBetween($state, DateTime $from, DateTime $to): float|int
    {
        $this->validateState($state);

        // work day seconds
        $workdayStartHour = 8;
        $workdayEndHour = 18;

        // work days between dates, minus 1 day
        $numberOfWorkingDays = max(0, $this->getNumberOfWorkingDaysBetween($state, $from, $to) - 1);

        // 8am to 6pm
        $fromHours = min($workdayEndHour, max($workdayStartHour, $from->format("H")));
        $fromMinutes = $from->format("i");
        if ($fromHours >= 18) {
            $fromMinutes = 0;
        }

        $toHours = min($workdayEndHour, max($workdayStartHour, $to->format("H")));
        $toMinutes = $to->format("i");
        if ($toHours >= 18) {
            $toMinutes = 0;
        }

        $startTimeInSeconds = $fromHours * 3600 + $fromMinutes * 60;
        $endTimeInSeconds = $toHours * 3600 + $toMinutes * 60;

        $numberOfSecondsInWorkDay = ($workdayEndHour - $workdayStartHour) * 3600;

        // calculate number of hours difference, 10 working hours per day
        return (($numberOfWorkingDays * $numberOfSecondsInWorkDay) + $endTimeInSeconds - $startTimeInSeconds) / 86400 * 24;
    }

    /**
     * I am in a habit of making all my functions public as I unit test every function individually and mock out any
     * sub functions the function I am testing calls
     * @param $state
     * @param DateTime $from
     * @param DateTime $to
     * @return int
     * @throws Exception
     */
    public function getNumberOfWorkingDaysBetween($state, DateTime $from, DateTime $to): int
    {
        $this->validateState($state);
        $days = 0;
        while ($from < $to) {
            if ($this->isDateOnAPublicHoliday($state, $from) && $this->isDateOnAWeekend($state)) {
                $days++;
            }
            $from->modify('+1 day');
        }
        return $days;
    }

    public function isDateOnAWeekend(DateTime $dateTime): bool
    {
        return in_array($dateTime->format('l'), ["Saturday", "Sunday"]);
    }

    public function isDateOnAPublicHoliday($state, DateTime $dateTime): bool
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

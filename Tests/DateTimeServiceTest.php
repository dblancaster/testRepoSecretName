<?php

namespace Tests;

use Services\DateTimeService;

class DateTimeServiceTest
{

    public DateTimeService $dateTimeService;

    public function __construct()
    {
        $this->dateTimeService = new DateTimeService();
    }

    public function getTestData()
    {
        return [
            ['from' => strtotime("2021-03-04 02:00:00"), 'to' => strtotime("2021-03-04 20:00:00"), 'expected' => 10.0],
            ['from' => strtotime("2021-03-04 10:00:00"), 'to' => strtotime("2021-03-04 11:30:00"), 'expected' => 1.5],
            ['from' => strtotime("2021-03-04 08:00:00"), 'to' => strtotime("2021-03-04 08:30:00"), 'expected' => 0.5],
            ['from' => strtotime("2021-03-04 07:00:00"), 'to' => strtotime("2021-03-04 08:00:00"), 'expected' => 0],
            ['from' => strtotime("2021-03-04 17:30:00"), 'to' => strtotime("2021-03-04 18:00:00"), 'expected' => 0.5],
            ['from' => strtotime("2021-03-04 18:00:00"), 'to' => strtotime("2021-03-04 18:30:00"), 'expected' => 0],
            ['from' => strtotime("2021-03-04 18:00:00"), 'to' => strtotime("2021-03-05 08:00:00"), 'expected' => 0],
            ['from' => strtotime("2021-03-04 18:00:00"), 'to' => strtotime("2021-03-05 09:00:00"), 'expected' => 1.0],
            ['from' => strtotime("2021-03-04 18:00:00"), 'to' => strtotime("2021-03-10 08:00:00"), 'expected' => 30.0]
        ];
    }

    public function runTests()
    {
        foreach ($this->getTestData() as $testData) {
            $hours = $this->dateTimeService->getWorkingHoursBetween(
                DateTimeService::STATE_SA,
                (new \DateTime())->setTimestamp($testData['from']),
                (new \DateTime())->setTimestamp($testData['to'])
            );
            if ($hours !== $testData['expected']) {
                $string = "Expected " . $testData['expected'] . ", Actual " . $hours;
                print "<p>$string</p>";
            }
        }
    }

}

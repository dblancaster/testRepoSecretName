<?php

namespace Data;

/**
 * I am choosing a simple data object to represent a blob of JSON data from example_public_holidays.json
 * I am using simpler property names based on how they will actually be used
 *
 * Setters and Getters serve no real purpose here, JSON data should always be represented by a hard coded definable object
 * Easily overridden and usable for the purposes of unit tests
 */
class PublicHoliday {

    public $dateYMD;
    public $name;
    public $state;

}

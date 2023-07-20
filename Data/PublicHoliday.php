<?php

namespace Data;

/**
 * A simple data object to represent a piece JSON data from example_public_holidays.json
 * Using simpler property names based on how they will actually be used
 *
 * Setters and Getters serve no real purpose here, JSON data should mostly be represented by a hard coded
 * definable object and rarely by loosely defined array keys
 */
class PublicHoliday {

    public $dateYMD;
    public $name;
    public $state;

}

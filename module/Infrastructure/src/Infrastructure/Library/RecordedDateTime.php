<?php

namespace Infrastructure\Library;

use Infrastructure\Library\InvalidDateException;

use DateInterval;
use DateTime;
use DateTimeZone;

class RecordedDateTime extends DateTime
{
    ### CONSTRUCTOR

    /**
     * @var string $time
     */
    public function __construct(
        $time = null,
        DateTimeZone $timezone = null
    )
    {
        parent::__construct($time, $timezone);

        $this->internalDateTime = new DateTime($time, $timezone);
        $this->validate();
    }

    ### PUBLIC METHODS

    /**
     * @var DateInterval $interval
     * @throws InvalidDateException
     */
    public function add(
        $interval
    )
    {
        $this->internalDateTime = clone $this;
        $this->internalDateTime->add($interval);
        $this->validate();

        return parent::add($interval);
    }

    /**
     * @var string $format
     * @var string $time
     * @var DateTimeZone $object
     */
    public static function createFromFormat(
        $format,
        $time,
        $object = null
    )
    {
        $datetime = parent::createFromFormat($format, $time, $object);

        $recordedDateTime = new RecordedDateTime();
        $recordedDateTime->setTimestamp($datetime->getTimestamp());
        $recordedDateTime->setTimezone($datetime->getTimezone());
        return $recordedDateTime;
    }

    /**
     * @var string $format
     * @throws InvalidDateException
     */
    public function modify(
        $modify
    )
    {
        $this->internalDateTime = clone $this;
        $this->internalDateTime->modify($modify);
        $this->validate();

        return parent::modify($modify);
    }

    /**
     * @var int $year
     * @var int $month
     * @var int $day
     * @throws InvalidDateException
     */
    public function setDate(
        $year,
        $month,
        $day
    )
    {
        $this->internalDateTime = clone $this;
        $this->internalDateTime->setDate($year, $month, $day);
        $this->validate();

        return parent::setDate($year, $month, $day);
    }

    /**
     * @var int $year
     * @var int $week
     * @var int $day
     * @throws InvalidDateException
     */
    public function setISODate(
        $year,
        $week,
        $day = 1
    )
    {
        $this->internalDateTime = clone $this;
        $this->internalDateTime->setISODate($year, $week, $day);
        $this->validate();

        return parent::setISODate($year, $week, $day);
    }

    /**
     * @var int $hour
     * @var int $minute
     * @var int $second
     * @throws InvalidDateException
     */
    public function setTime(
        $hour,
        $minute,
        $second = 0
    )
    {
        $this->internalDateTime = clone $this;
        $this->internalDateTime->setTime($hour, $minute, $second);
        $this->validate();

        return parent::setTime($hour, $minute, $second);
    }

    /**
     * @var int $unixtimestamp
     * @throws InvalidDateException
     */
    public function setTimestamp(
        $unixtimestamp
    )
    {
        $this->internalDateTime = clone $this;
        $this->internalDateTime->setTimestamp($unixtimestamp);
        $this->validate();

        return parent::setTimestamp($unixtimestamp);
    }

    /**
     * @var DateTimeZone $timezone
     * @throws InvalidDateException
     */
    public function setTimezone(
        $timezone
    )
    {
        $this->internalDateTime = clone $this;
        $this->internalDateTime->setTimezone($timezone);
        $this->validate();

        return parent::setTimezone($timezone);
    }

    /**
     * @var DateInterval $interval
     * @throws InvalidDateException
     */
    public function sub(
        $interval
    )
    {
        $this->internalDateTime = clone $this;
        $this->internalDateTime->sub($interval);
        $this->validate();

        return parent::sub($interval);
    }

    ### PRIVATE MEMBERS

    /**
     * @var DateTime
     */
    private $internalDateTime;

    ### PRIVATE METHODS

    private function validate()
    {
        if ($this->internalDateTime > new DateTime()) {
            throw new InvalidDateException('RecordedDateTime must be in the past');
        }
    }
}

<?php

/**
 * Copyright 2014 SURFnet bv
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlock\DateTime;

use DateInterval;
use DateTime as CoreDateTime;
use OpenConext\EngineBlock\Exception\InvalidArgumentException;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class DateTime
{
    /**
     * The 'c' format, expanded in separate format characters. This string can also be used with
     * `DateTime::createFromString()`.
     */
    const FORMAT = 'Y-m-d\\TH:i:sP';

    /**
     * Allows for mocking of time.
     *
     * @var self|null
     */
    private static $now;

    /**
     * @var CoreDateTime
     */
    private $dateTime;

    /**
     * @param CoreDateTime|null $dateTime
     */
    public function __construct(CoreDateTime $dateTime = null)
    {
        $this->dateTime = $dateTime ?: new CoreDateTime();
    }

    /**
     * @return self
     */
    public static function now()
    {
        return self::$now ?: new self(new CoreDateTime());
    }

    /**
     * @param string $string A date-time string formatted using `self::FORMAT` (eg. '2014-11-26T15:20:43+01:00').
     *
     * @return self
     */
    public static function fromString($string)
    {
        if (!is_string($string)) {
            InvalidArgumentException::invalidType('string', 'dateTime', $string);
        }

        $dateTime = CoreDateTime::createFromFormat(self::FORMAT, $string);

        if ($dateTime === false) {
            throw new \InvalidArgumentException('Date-time string could not be parsed: is it formatted correctly?');
        }

        return new self($dateTime);
    }

    /**
     * @param DateInterval $interval
     *
     * @return DateTime
     */
    public function add(DateInterval $interval)
    {
        $dateTime = clone $this->dateTime;
        $dateTime->add($interval);

        return new self($dateTime);
    }

    /**
     * @param DateInterval $interval
     *
     * @return DateTime
     */
    public function sub(DateInterval $interval)
    {
        $dateTime = clone $this->dateTime;
        $dateTime->sub($interval);

        return new self($dateTime);
    }

    /**
     * @param DateTime $dateTime
     *
     * @return boolean
     */
    public function comesBefore(DateTime $dateTime)
    {
        return $this->dateTime < $dateTime->dateTime;
    }

    /**
     * @param DateTime $dateTime
     *
     * @return boolean
     */
    public function comesBeforeOrIsEqual(DateTime $dateTime)
    {
        return $this->dateTime <= $dateTime->dateTime;
    }

    /**
     * @param DateTime $dateTime
     *
     * @return boolean
     */
    public function comesAfter(DateTime $dateTime)
    {
        return $this->dateTime > $dateTime->dateTime;
    }

    /**
     * @param DateTime $dateTime
     *
     * @return boolean
     */
    public function comesAfterOrIsEqual(DateTime $dateTime)
    {
        return $this->dateTime >= $dateTime->dateTime;
    }

    /**
     * @param $format
     *
     * @return string
     */
    public function format($format)
    {
        $formatted = $this->dateTime->format($format);

        if ($formatted === false) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Given format "%s" is not a valid format for DateTime',
                    $format
                )
            );
        }

        return $formatted;
    }

    /**
     * @return string An ISO 8601 representation of this DateTime.
     */
    public function __toString()
    {
        return $this->format(self::FORMAT);
    }
}

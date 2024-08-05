<?php
declare(strict_types=1);
namespace Yanselmask\Openable\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\OpeningHours\Day;
use Spatie\OpeningHours\OpeningHoursForDay;
use Spatie\OpeningHours\OpeningHours;

trait Openable
{
    /**
     * The resource may have many shifts.
     *
     * @return MorphMany
     */
    public function shifts(): MorphMany
    {
        return $this->morphMany(\Yanselmask\Openable\Models\Openable::class, 'openable');
    }
    /**
     * The resource may have many shifts actived.
     *
     * @return MorphMany
     */
    public function shiftsActived(): MorphMany
    {
        return $this->shifts()
                    ->where('is_actived',true);
    }
    /**
     * The resource may have many shifts Inactived.
     *
     * @return MorphMany
     */
    public function shiftsInactived(): MorphMany
    {
        return $this->shifts()
                    ->where('is_actived',false);
    }
    /**
     * The resource by default
     *
     * @return \Yanselmask\Openable\Models\Openable
     */
    public function shiftByDefault(): \Yanselmask\Openable\Models\Openable
    {
        return $this->shiftsActived()
                    ->where('is_default',true)
                    ->first();
    }
    /**
     * The resource may have many bookings.
     *
     * @return OpeningHours
     */
    private function openingHours(): OpeningHours
    {
        $data = $this->shiftByDefault();
        $openingHours = OpeningHours::create($data?->data);
        return $openingHours;
    }
    /**
     * Returns a Spatie\OpeningHours\TimeRange instance of the current open range if the business is open, false if the business is closed.
     * @param \DateTimeInterface $time
     * @return void
     */
    public function currentOpenRange(\DateTimeInterface $time = null): void
    {
        if(!$time)
        {
            $time = new \DateTime('now');
        }

        $range = $this->openingHours()->currentOpenRange($time);

        if ($range) {
            echo "It's open since ".$range->start()."\n";
            echo "It will close at ".$range->end()."\n";
        } else {
            echo "It's closed since ".$this->openingHours()->previousClose($time)->format('l H:i')."\n";
            echo "It will re-open at ".$this->openingHours()->nextOpen($time)->format('l H:i')."\n";
        }
    }
    /**
     * Returns a Spatie\OpeningHours\TimeRange instance of the current open range if the business is open, false if the business is closed.
     * @param \DateTimeInterface $time
     * @return void
     */
    public function currentOpenRangeStart(\DateTimeInterface $time): void
    {
        $range = $this->openingHours()->currentOpenRangeStart($time);
         if($range)
         {
             echo "It's open since ".$range->format('H:i');
         } else{
             echo "It's closed";
         }
    }
    /**
     * Returns a Spatie\OpeningHours\TimeRange instance of the current open range if the business is open, false if the business is closed.
     * @param \DateTimeInterface $time
     * @return void
     */
    public function currentOpenRangeEnd(\DateTimeInterface $time): void
    {
        $range = $this->openingHours()->currentOpenRangeEnd($time);
        if($range)
        {
            echo "It will close at ".$range->format('H:i');
        } else{
            echo "It's closed";
        }

    }
    /**
     * The object can be queried for a day in the week, which will return a result based on the regular schedule
     * @param string $time
     */
    public function isOpenOn(String $time): bool
    {
        return $this->openingHours()->isOpenOn($time);
    }
    /**
     * It can also be queried for a specific date and time
     * @param \DateTime $time
     */
    public function isOpenAt($time): bool
    {
        return $this->openingHours()->isOpenAt($time);
    }
    /**
     * OpeningHoursForDay object for the regular schedule
     * @param Day|string $time
     * @return OpeningHoursForDay
     */
    public function forDay($time): OpeningHoursForDay
    {
        return $this->openingHours()->forDay($time);
    }
    /**
     * OpeningHoursForDay[] for the regular schedule, keyed by day name
     * @return array
     */
    public function forWeek(): array
    {
        return $this->openingHours()->forWeek();
    }
    /**
     * Array of day with same schedule for the regular schedule, keyed by day name, days combined by working hours
     * @return array
     */
    public function forWeekCombined(): array
    {
        return $this->openingHours()->forWeekCombined();
    }
    /**
     * Returns an array of concatenated days, adjacent days with the same hours. Array key is first day with same hours, array values are days that have the same working hours and OpeningHoursForDay object.
     * @return array
     */
    public function forWeekConsecutiveDays(): array
    {
        return $this->openingHours()->forWeekConsecutiveDays();
    }
    /**
     * OpeningHoursForDay object for a specific day
     * @param \DateTime $time
     * @return OpeningHoursForDay
     */
    public function forDate($time): OpeningHoursForDay
    {
        return $this->openingHours()->forDate($time);
    }
    /**
     * OpeningHoursForDay[] of all exceptions, keyed by date
     * @return array
     */
    public function exceptions(): array
    {
        return $this->openingHours()->exceptions();
    }
    /**
     * It can also return the next open or close DateTime from a given DateTime
     * @param \DateTimeInterface $time|null
     * @return \DateTimeInterface
     */
    public function nextOpen(\DateTimeInterface $time = null): \DateTimeInterface
    {
        return $this->openingHours()->nextOpen($time);
    }
    /**
     * It can also return the next open or close DateTime from a given DateTime
     * @param \DateTimeInterface $time|null
     * @return \DateTimeInterface
     */
    public function nextClose(\DateTimeInterface $time = null): \DateTimeInterface
    {
        return $this->openingHours()->nextClose($time);
    }
    /**
     * Checks if the business is closed on a day in the regular schedule.
     * @param string $time
     * @return bool
     */
    public function isClosedOn(string $time): bool
    {
        return $this->openingHours()->isClosedOn($time);
    }
    /**
     * Checks if the business is closed on a specific day, at a specific time.
     * @param  $time
     * @return bool
     */
    public function isClosedAt( $time): bool
    {
        return $this->openingHours()->isClosedAt($time);
    }
    /**
     * Checks if the business is open right now.
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->openingHours()->isOpen();
    }
    /**
     * Checks if the business is closed right now.
     * @return bool
     */
    public function isClosed(): bool
    {
        return $this->openingHours()->isClosed();
    }
    /**
     * Checks if the business is open 24/7, has no exceptions and no filters.
     * @return bool
     */
    public function isAlwaysOpen(): bool
    {
        return $this->openingHours()->isAlwaysOpen();
    }
    /**
     * Checks if the business is open 24/7, has no exceptions and no filters.
     * @return bool
     */
    public function isAlwaysClosed(): bool
    {
        return $this->openingHours()->isAlwaysClosed();
    }
    /**
     * Return the amount of open time (number of hours as a floating number) between 2 dates/times.
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return float
     */
    public function diffInOpenHours(\DateTimeInterface $startDate, \DateTimeInterface $endDate): float
    {
        return $this->openingHours()->diffInOpenHours($startDate, $endDate);
    }
    /**
     * Return the amount of open time (number of minutes as a floating number) between 2 dates/times.
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return float
     */
    public function diffInOpenMinutes(\DateTimeInterface $startDate, \DateTimeInterface $endDate): float
    {
        return $this->openingHours()->diffInOpenMinutes($startDate, $endDate);
    }
    /**
     * Return the amount of open time (number of seconds as a floating number) between 2 dates/times.
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return float
     */
    public function diffInOpenSeconds(\DateTimeInterface $startDate, \DateTimeInterface $endDate): float
    {
        return $this->openingHours()->diffInOpenSeconds($startDate, $endDate);
    }
    /**
     * Return the amount of closed time (number of hours as a floating number) between 2 dates/times.
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return float
     */
    public function diffInClosedHours(\DateTimeInterface $startDate, \DateTimeInterface $endDate): float
    {
        return $this->openingHours()->diffInClosedHours($startDate, $endDate);
    }
    /**
     * Return the amount of closed time (number of minutes as a floating number) between 2 dates/times.
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return float
     */
    public function diffInClosedMinutes(\DateTimeInterface $startDate, \DateTimeInterface $endDate): float
    {
        return $this->openingHours()->diffInClosedMinutes($startDate, $endDate);
    }
    /**
     * Return the amount of closed time (number of seconds as a floating number) between 2 dates/times.
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return float
     */
    public function diffInClosedSeconds(\DateTimeInterface $startDate, \DateTimeInterface $endDate): float
    {
        return $this->openingHours()->diffInClosedSeconds($startDate, $endDate);
    }
    /**
     * Returns previous open DateTime from the given DateTime ($dateTime or from now if this parameter is null or omitted).
     * @param \DateTimeInterface $time
     * @return \DateTimeInterface
     */
    public function previousOpen($time): \DateTimeInterface
    {
        return $this->openingHours()->previousOpen($time);
    }
    /**
     * Returns previous close DateTime from the given DateTime ($dateTime or from now if this parameter is null or omitted).
     * @param \DateTimeInterface $time
     * @return \DateTimeInterface
     */
    public function previousClose(\DateTimeInterface $time): \DateTimeInterface
    {
        return $this->openingHours()->previousClose($time);
    }
}

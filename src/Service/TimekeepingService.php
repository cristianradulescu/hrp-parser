<?php

namespace App\Service;

/**
 * Class TimekeepingService
 * @package App\Service
 */
class TimekeepingService
{
    /**
     * @param string $workedHours
     * @return array
     * @throws \Exception
     */
    public function computeDailyDetails(string $workedHours)
    {
        if ('8' !== $workedHours) {
            return ['-', '-', '-', $workedHours];
        }

        // compute start and end hours, wih a difference between 7h48m and 8h17m, +1 h break
        $startHour = new \DateTime('08:'.rand(32, 59));
        $endHour = (clone $startHour)->add(new \DateInterval('PT'.rand(8*60+48, 9*60+17).'M'));

        return [
            $startHour->format('H:i'),
            $endHour->format('H:i'),
            '1h',
            $endHour->diff($startHour)->format('%hh%im')
        ];
    }

    /**
     * @param $year
     * @param $month
     * @param string $locale
     * @param int $datetype
     *
     * @return array
     */
    public function createListOfIntlDatesInMonth($year, $month, $locale = 'en', $datetype = \IntlDateFormatter::MEDIUM)
    {
        $list = [];
        $nbOfDaysInMonth = (new \DateTime($year.'-'.$month.'-01'))->format('t');
        for ($index = 1; $index <= $nbOfDaysInMonth; $index++) {
            $list[$index] = (new \IntlDateFormatter(
                $locale,
                $datetype,
                \IntlDateFormatter::NONE)
            )->format(new \DateTime($year . '-' . $month . '-' . $index));
        }

        return $list;
    }
}

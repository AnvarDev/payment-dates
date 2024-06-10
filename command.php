<?php

/**
 * Class ApplicationCommand.
 */
class ApplicationCommand
{
    /**
     * Payments dates.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * On the 15th of every month bonuses are paid for the previous month, unless that day is a weekend.
     *
     * @var int
     */
    protected $bonusDay = 15;

    /**
     * In that case, they are paid the first Wednesday after the 15th.
     *
     * @var string
     */
    protected $nextBonusDay = 'Wednesday';

    /**
     * Returns the modified DateTime object.
     *
     * @var int $month
     * @var int|null $day
     * @return DateTime
     */
    protected function getDateTime(int $month, int $day = null): DateTime
    {
        $date = new DateTime();
        $date->setDate($date->format('Y'), $month, $day ?? 1);

        return $date;
    }

    /**
     * Check the date is weekend.
     *
     * @var DateTime $date
     * @return bool
     */
    protected function isWeekend(DateTime $date): bool
    {
        if ($date->format('N') >= 6) {
            return true;
        }

        return false;
    }

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->dates[] = [
            'Month',
            'Salary Payment Date',
            'Bonus Payment Date',
        ];

        for ($m = date('n'); $m <= 12; $m++) {
            $date = $this->getDateTime($m, $this->bonusDay);

            if ($this->isWeekend($date)) {
                $date->modify('next ' . $this->nextBonusDay);
            }
            $bonusDate = $date->format('d.m.Y');

            $date->modify('last day of this month');
            if ($this->isWeekend($date)) {
                $date->modify('previous Friday');
            }
            $salaryDate = $date->format('d.m.Y');

            $this->dates[] = [
                $date->format('F'),
                $salaryDate,
                $bonusDate,
            ];
        }
    }

    /**
     * Returns CSV output of payments dates.
     *
     * @return void
     */
    public function download(): void
    {
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="payment_dates.csv";');

        $handle = fopen('php://output', 'w');

        foreach ($this->dates as $line) {
            fputcsv($handle, $line, ',');
        }

        fclose($handle);
    }
}

(new ApplicationCommand())->download();

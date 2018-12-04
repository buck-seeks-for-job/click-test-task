<?php
declare(strict_types=1);

namespace Click\MeasurementCalculator\Domain;

use Click\MeasurementCalculator\Domain\SourceDataRow;

class MeasurementCalculationSummary
{
    /**
     * @var \DateTimeInterface
     */
    private $date;

    /**
     * @var int
     */
    private $summaryA = 0;

    /**
     * @var int
     */
    private $summaryB = 0;

    /**
     * @var float
     */
    private $summaryC = 0;

    public function __construct(\DateTimeInterface $date)
    {
        $this->date = $date;
    }

    public function add(SourceDataRow $sourceDataRow): void
    {
        $this->summaryA += $sourceDataRow->getMeasurementA();
        $this->summaryB += $sourceDataRow->getMeasurementB();
        $this->summaryC += $sourceDataRow->getMeasurementC();
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getSummaryA(): int
    {
        return $this->summaryA;
    }

    public function getSummaryB(): int
    {
        return $this->summaryB;
    }

    public function getSummaryC(): float
    {
        return $this->summaryC;
    }
}

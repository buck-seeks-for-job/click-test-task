<?php

namespace Click\MeasurementCalculator\Domain;

class SourceDataRow
{
    /**
     * @var \DateTimeInterface
     */
    private $date;

    /**
     * @var int
     */
    private $measurementA;

    /**
     * @var int
     */
    private $measurementB;

    /**
     * @var float
     */
    private $measurementC;

    public function __construct(\DateTimeInterface $date, int $measurementA, int $measurementB, float $measurementC)
    {
        $this->date = $date;
        $this->measurementA = $measurementA;
        $this->measurementB = $measurementB;
        $this->measurementC = $measurementC;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getMeasurementA(): int
    {
        return $this->measurementA;
    }

    public function getMeasurementB(): int
    {
        return $this->measurementB;
    }

    public function getMeasurementC(): float
    {
        return $this->measurementC;
    }
}

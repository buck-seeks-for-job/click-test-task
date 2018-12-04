<?php

namespace Click\MeasurementCalculator\Domain;

use Click\MeasurementCalculator\Domain\SourceDataRow;

interface SourceDataGroupPerformer
{
    public function add(SourceDataRow $sourceDataRow): void;

    /**
     * @return \Generator|\DateTimeInterface[]
     */
    public function fetchDates(): \Generator;

    /**
     * @param \DateTimeInterface $date
     * @return \Generator|SourceDataRow[]
     */
    public function fetchRows(\DateTimeInterface $date): \Generator;
}
<?php

namespace Click\MeasurementCalculator\Domain;

use Click\MeasurementCalculator\Domain\SourceDataRow;

interface SourceDataRowFetcher
{
    /**
     * @param string $fileName
     * @return \Generator|SourceDataRow[]
     */
    public function fetch(string $fileName): \Generator;
}

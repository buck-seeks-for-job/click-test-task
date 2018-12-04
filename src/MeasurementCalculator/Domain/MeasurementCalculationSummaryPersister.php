<?php
declare(strict_types=1);

namespace Click\MeasurementCalculator\Domain;

use Click\MeasurementCalculator\Domain\MeasurementCalculationSummary;

interface MeasurementCalculationSummaryPersister
{
    public function persist(MeasurementCalculationSummary $measurementCalculationSummary): void;
}

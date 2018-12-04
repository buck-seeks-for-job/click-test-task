<?php
declare(strict_types=1);

namespace Click\MeasurementCalculator\Application;

use Click\MeasurementCalculator\Domain\FileNameFetcher;
use Click\MeasurementCalculator\Domain\MeasurementCalculationSummary;
use Click\MeasurementCalculator\Domain\MeasurementCalculationSummaryPersister;
use Click\MeasurementCalculator\Domain\SourceDataGroupPerformer;
use Click\MeasurementCalculator\Domain\SourceDataRowFetcher;

class MeasurementCalculator
{
    /**
     * @var FileNameFetcher
     */
    private $fileNameFetcher;

    /**
     * @var SourceDataRowFetcher
     */
    private $sourceDataRowFetcher;

    /**
     * @var SourceDataGroupPerformer
     */
    private $sourceDataGroupPerformer;

    /**
     * @var MeasurementCalculationSummaryPersister
     */
    private $measurementCalculationSummaryPersister;

    public function __construct(
        FileNameFetcher $fileNameFetcher,
        SourceDataRowFetcher $sourceDataRowFetcher,
        SourceDataGroupPerformer $sourceDataGroupPerformer,
        MeasurementCalculationSummaryPersister $measurementCalculationSummaryPersister
    ) {
        $this->fileNameFetcher = $fileNameFetcher;
        $this->sourceDataRowFetcher = $sourceDataRowFetcher;
        $this->sourceDataGroupPerformer = $sourceDataGroupPerformer;
        $this->measurementCalculationSummaryPersister = $measurementCalculationSummaryPersister;
    }

    public function calculate(string $sourcePath): void
    {
        $this->fetchAndGroupSourceData($sourcePath);
        $this->sumAndPersistGroupedData();
    }

    private function fetchAndGroupSourceData(string $sourcePath): void
    {
        foreach ($this->fileNameFetcher->fetchFilenames($sourcePath) as $fileName) {
            foreach ($this->sourceDataRowFetcher->fetch($fileName) as $row) {
                $this->sourceDataGroupPerformer->add($row);
            }
        }
    }

    private function sumAndPersistGroupedData(): void
    {
        foreach ($this->sourceDataGroupPerformer->fetchDates() as $date) {
            $summary = new MeasurementCalculationSummary($date);

            foreach ($this->sourceDataGroupPerformer->fetchRows($date) as $row) {
                $summary->add($row);
            }

            $this->measurementCalculationSummaryPersister->persist($summary);
        }
    }
}

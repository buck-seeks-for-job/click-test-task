<?php
declare(strict_types=1);

namespace Click\MeasurementCalculator\Infrastructure\Csv;

use Click\MeasurementCalculator\Domain\FileNameFetcher;
use Click\MeasurementCalculator\Domain\MeasurementCalculationSummary;
use Click\MeasurementCalculator\Domain\MeasurementCalculationSummaryPersister;
use Click\MeasurementCalculator\Domain\SourceDataGroupPerformer;
use Click\MeasurementCalculator\Domain\SourceDataRow;
use Click\MeasurementCalculator\Domain\SourceDataRowFetcher;

class CommonCsvGateway implements SourceDataRowFetcher, MeasurementCalculationSummaryPersister, SourceDataGroupPerformer
{
    private const DELIMITER = ';';

    /**
     * @var string
     */
    private $outputFileName;

    /**
     * @var string
     */
    private $temporaryDirectory;

    /**
     * @var FileNameFetcher
     */
    private $fileNameFetcher;

    /**
     * @var bool
     */
    private $isFirstPersist = true;

    public function __construct(string $outputFileName, string $temporaryDirectory, FileNameFetcher $fileNameFetcher)
    {
        $this->outputFileName = $outputFileName;
        $this->temporaryDirectory = $temporaryDirectory;
        $this->fileNameFetcher = $fileNameFetcher;
    }

    public function fetch(string $fileName): \Generator
    {
        $file = $this->openFile($fileName, 'r');

        while (!$file->eof()) {
            $row = $file->fgetcsv(self::DELIMITER);

            if (empty($row[0]) || !preg_match("/^\d{4}-\d{2}-\d{2}$/ui", $row[0])) {
                continue;
            }

            yield new SourceDataRow(new \DateTimeImmutable($row[0]), (int)$row[1], (int)$row[2], (float)$row[3]);
        }
    }

    public function persist(MeasurementCalculationSummary $measurementCalculationSummary): void
    {
        $file = $this->openFile($this->outputFileName, 'a');

        if ($this->isFirstPersist) {
            $file->fputcsv(['date', 'A', 'B', 'C'], self::DELIMITER);
            $this->isFirstPersist = false;
        }

        $file->fputcsv([
            $measurementCalculationSummary->getDate()->format('Y-m-d'),
            $measurementCalculationSummary->getSummaryA(),
            $measurementCalculationSummary->getSummaryB(),
            $measurementCalculationSummary->getSummaryC()
        ], self::DELIMITER);
    }

    public function add(SourceDataRow $sourceDataRow): void
    {
        $file = $this->openFile($this->temporaryDirectory . '/' . $sourceDataRow->getDate()->format('Y-m-d') . '.csv', 'a');

        $file->fputcsv([
            $sourceDataRow->getDate()->format('Y-m-d'),
            $sourceDataRow->getMeasurementA(),
            $sourceDataRow->getMeasurementB(),
            $sourceDataRow->getMeasurementC()
        ], self::DELIMITER);
    }

    public function fetchDates(): \Generator
    {
        foreach ($this->fileNameFetcher->fetchFileNames($this->temporaryDirectory) as $fileName) {
            $dateStr = preg_replace("/^.*(\d{4}-\d{2}-\d{2})\.csv$/ui", "$1", $fileName);

            yield new \DateTimeImmutable($dateStr);
        }
    }

    public function fetchRows(\DateTimeInterface $date): \Generator
    {
        $file = $this->openFile($this->temporaryDirectory . '/' . $date->format('Y-m-d') . '.csv', 'r');

        while (!$file->eof()) {
            $row = $file->fgetcsv(self::DELIMITER);

            if (empty($row[0])) {
                continue;
            }

            yield new SourceDataRow(new \DateTimeImmutable($row[0]), (int)$row[1], (int)$row[2], (float)$row[3]);
        }
    }

    protected function openFile(string $fileName, string $mode): \SplFileObject
    {
        return new \SplFileObject($fileName, $mode);
    }

    public function __destruct()
    {
	    exec('rm -rf ' . realpath($this->temporaryDirectory));
    }
}

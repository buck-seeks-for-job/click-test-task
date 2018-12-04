<?php
declare(strict_types=1);

namespace Click\Test\MeasurementCalculator\Application;

use Click\MeasurementCalculator\Domain\FileNameFetcher;
use Click\MeasurementCalculator\Application\MeasurementCalculator;
use Click\MeasurementCalculator\Infrastructure\Csv\CommonCsvGateway;
use PHPUnit\Framework\TestCase;

class MeasurementCalculatorTest extends TestCase implements FileNameFetcher
{
    /**
     * @var MeasurementCalculator
     */
    private $measurementCalculator;

    /**
     * @var CommonCsvGateway
     */
    private $csvGateway;

    protected function setUp()
    {
        $this->csvGateway = $this->createTestableCommonCsvGateway();

        $this->measurementCalculator = new MeasurementCalculator(
            $this,
            $this->csvGateway,
            $this->csvGateway,
            $this->csvGateway
        );
    }

    /**
     * @test
     */
    public function calculate_GivenNoFilesInSourceDirectory_PersistsNothing(): void
    {
        $this->givenSourceFile('/home/karabas_barabas/bar.csv', [['2018-01-01', 1, 2, 3.0]]);

        $this->measurementCalculator->calculate('/home/foo');

        $this->assertThatNothingPersisted();
    }

    /**
     * @test
     */
    public function calculate_GivenEmptyFileInSourceDirectory_PersistsNothing(): void
    {
        $this->givenSourceFile('/home/karabas_barabas/bar.csv', []);

        $this->measurementCalculator->calculate('/home/karabas_barabas');

        $this->assertThatNothingPersisted();
    }

    /**
     * @test
     */
    public function calculate_GivenFileWithSingleRow_PersistsThatRow(): void
    {
        $this->givenSourceFile('/home/karabas_barabas/bar.csv', [$row = ['2018-01-01', 1, 2, 3.0]]);

        $this->measurementCalculator->calculate('/home/karabas_barabas');

        $this->assertThatCertainSummaryPersisted([$row]);
    }

    /**
     * @test
     */
    public function calculate_GivenFileWithTwoRowsWithDifferentDate_PersistsThoseRows(): void
    {
        $this->givenSourceFile('/home/karabas_barabas/bar.csv', [
            $row1 = ['2018-01-01', 1, 2, 3.0],
            $row2 = ['2018-01-02', 2, 3, 4.0]
        ]);

        $this->measurementCalculator->calculate('/home/karabas_barabas');

        $this->assertThatCertainSummaryPersisted([$row1, $row2]);
    }

    /**
     * @test
     */
    public function calculate_GivenFileWithTwoRowsWithTheSameDate_PersistsSummaryRow(): void
    {
        $this->givenSourceFile('/home/karabas_barabas/bar.csv', [
            $row1 = ['2018-01-01', 1, 2, 3.0],
            $row2 = ['2018-01-01', 2, 3, 4.5]
        ]);

        $this->measurementCalculator->calculate('/home/karabas_barabas');

        $this->assertThatCertainSummaryPersisted([['2018-01-01', 3, 5, 7.5]]);
    }

    /**
     * @test
     */
    public function calculate_GivenTwoFilesWithOneRowEachWithDifferentDate_PersistsThoseRows(): void
    {
        $this->givenSourceFile('/home/karabas_barabas/foo.csv', [
            $row1 = ['2018-01-01', 1, 2, 3.0]
        ]);
        $this->givenSourceFile('/home/karabas_barabas/bar.csv', [
            $row2 = ['2018-01-02', 2, 3, 4.5]
        ]);

        $this->measurementCalculator->calculate('/home/karabas_barabas');

        $this->assertThatCertainSummaryPersisted([$row1, $row2]);
    }

    /**
     * @test
     */
    public function calculate_GivenTwoFilesWithOneRowEachWithTheSameDate_PersistsSummaryRow(): void
    {
        $this->givenSourceFile('/home/karabas_barabas/foo.csv', [
            $row1 = ['2018-01-01', 1, 2, 3.0]
        ]);
        $this->givenSourceFile('/home/karabas_barabas/bar.csv', [
            $row2 = ['2018-01-01', 2, 3, 4.5]
        ]);

        $this->measurementCalculator->calculate('/home/karabas_barabas');

        $this->assertThatCertainSummaryPersisted([['2018-01-01', 3, 5, 7.5]]);
    }

    /**
     * @test
     */
    public function calculate_GivenTwoFilesWithTwoRowsEachWithTheSameDate_PersistsSummaryRow(): void
    {
        $this->givenSourceFile('/home/karabas_barabas/foo.csv', [
            $row1 = ['2018-01-01', 1, 1, 1.0],
            $row2 = ['2018-01-01', 1, 1, 1.0]
        ]);
        $this->givenSourceFile('/home/karabas_barabas/bar.csv', [
            $row3 = ['2018-01-01', 1, 1, 1.0],
            $row4 = ['2018-01-01', 1, 1, 1.0]
        ]);

        $this->measurementCalculator->calculate('/home/karabas_barabas');

        $this->assertThatCertainSummaryPersisted([['2018-01-01', 4, 4, 4.0]]);
    }

    public function fetchFileNames(string $sourcePath): \Generator
    {
        foreach (array_keys($this->csvGateway->fileMap) as $fileName) {
            if (preg_match("/^" . preg_quote($sourcePath, '/') . "/ui", $fileName)) {
                yield $fileName;
            }
        }
    }

    private function givenSourceFile(string $fileName, array $data): void
    {
        $data = array_merge([['date', 'A', 'B', 'C']], $data);

        $this->csvGateway->givenSourceFile($fileName, $data);
    }

    private function assertThatNothingPersisted(): void
    {
        $this->csvGateway->assertThatNothingPersisted();
    }

    private function assertThatCertainSummaryPersisted(array $summary): void
    {
        $summary = array_merge([['date', 'A', 'B', 'C']], $summary);

        $this->csvGateway->assertThatCertainSummaryPersisted($summary);
    }

    private function createTestableCommonCsvGateway()
    {
        return new class('output.csv', 'tmp', $this) extends CommonCsvGateway
        {
            /**
             * @var \SplFileObject[]
             */
            public $fileMap = [];

            public function givenSourceFile(string $fileName, array $data): void
            {
                $file = $this->openFile($fileName, 'w+');

                foreach ($data as $datum) {
                    $file->fputcsv($datum, ';');
                }

                $file->rewind();
            }

            public function assertThatNothingPersisted(): void
            {
                $persistedData = $this->fetchPersistedData();

                assertThat($persistedData, is(emptyArray()));
            }

            public function assertThatCertainSummaryPersisted(array $summary): void
            {
                $persistedData = $this->fetchPersistedData();

                assertThat($persistedData, containsInAnyOrder($summary));
            }

            public function __destruct()
            {
            }

            protected function openFile(string $fileName, string $mode): \SplFileObject
            {
                if (!array_key_exists($fileName, $this->fileMap)) {
                    $this->fileMap[$fileName] = new \SplFileObject('php://memory', 'w+');
                }

                if ($mode === 'r' ) {
                    $this->fileMap[$fileName]->rewind();
                }

                return $this->fileMap[$fileName];
            }

            private function fetchPersistedData(): array
            {
                if (!array_key_exists('output.csv', $this->fileMap)) {
                    return [];
                }

                $file = $this->fileMap['output.csv'];
                $file->rewind();
                $persistedData = [];
                while (!$file->eof()) {
                    $row = $file->fgetcsv(';');
                    if (empty($row[0])) {
                        continue;
                    }

                    $persistedData[] = $row;
                }

                return $persistedData;
            }
        };
    }
}

<?php
declare(strict_types=1);

namespace Click\MeasurementCalculator\Infrastructure\FileSystem;

use Click\MeasurementCalculator\Domain\FileNameFetcher;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

class RecursiveFileNameFetcher implements FileNameFetcher
{
    public function fetchFileNames(string $sourcePath): \Generator
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourcePath));
        $regexIterator = new RegexIterator($iterator, '/^.+\.csv$/ui', RecursiveRegexIterator::GET_MATCH);

        foreach ($regexIterator as $fileName) {
            yield $fileName[0];
        }
    }
}

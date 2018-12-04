<?php

namespace Click\MeasurementCalculator\Domain;

interface FileNameFetcher
{
    /**
     * @param string $sourcePath
     * @return \Generator|string[]
     */
    public function fetchFileNames(string $sourcePath): \Generator;
}

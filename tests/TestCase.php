<?php

namespace Krixon\Math\Test;

class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function extractDataSetColumn(array $dataSets, int $column)
    {
        return array_map(function (array $dataSet) use ($column) {
            return [$dataSet[$column]];
        }, $dataSets);
    }
    
    
    protected function swapDataSetColumns(array $dataSets, array $swaps)
    {
        return array_map(function (array $dataSet) use ($swaps) {
            $newDataSet = [];
            foreach ($dataSet as $column => $value) {
                foreach ($swaps as $a => $b) {
                    if ($a === $column) {
                        $newDataSet[$column] = $dataSet[$b];
                    } elseif ($b === $column) {
                        $newDataSet[$column] = $dataSet[$a];
                    } else {
                        $newDataSet[$column] = $value;
                    }
                }
            }
            return $newDataSet;
        }, $dataSets);
    }
}

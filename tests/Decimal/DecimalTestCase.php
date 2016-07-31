<?php

namespace Krixon\Math\Test\Decimal;

use Krixon\Math\Test\TestCase;

/**
 * @coversDefaultClass Krixon\Math\Decimal
 * @covers ::<protected>
 * @covers ::<private>
 */
class DecimalTestCase extends TestCase
{
    public function stringProvider()
    {
        return $this->extractDataSetColumn($this->stringToExpectedStringProvider(), 0);
    }
    
    
    public function stringToExpectedStringProvider()
    {
        return [
            ['0', '0', 0],
            ['-0', '0', 0],
            ['+0', '0', 0],
            ['1', '1', 0],
            ['-1', '-1', 0],
            ['+1', '1', 0],
            ['123456789', '123456789', 0],
            ['-123456789', '-123456789', 0],
            ['+123456789', '123456789', 0],
            ['123456789.987654321', '123456789.987654321', 9],
            ['-123456789.987654321', '-123456789.987654321', 9],
            ['+123456789.987654321', '123456789.987654321', 9],
            ['0', '0.0', 1],
            ['0', '0.0000000000', 10],
            ['0', '0.00000000000000000000000000000000000000000000000000', 50],
        ];
    }
    
    
    public function floatProvider()
    {
        return $this->extractDataSetColumn($this->floatToExpectedStringProvider(), 0);
    }
    
    
    public function floatToExpectedStringProvider()
    {
        return [
            [0.0, '0', 0],
            [0.0, '0.0', 1],
            [0.0, '0.0000000000', 10],
            [0.0, '0.00000000000000000000000000000000000000000000000000', 50],
            [-0.0, '0.0', 1],
            [1.0, '1', 0],
            [-1.0, '-1', 0],
            [123456789.0, '123456789.00', 2],
            [-123456789.0, '-123456789.00', 2],
            [123456789.987654321, '123456789.987654328', 9],   // Floating point imprecision.
            [-123456789.987654321, '-123456789.987654328', 9], // Floating point imprecision.
            [123456789.987654321, '123456789.988', 3],         // Floating point imprecision.
            [-123456789.987654321, '-123456789.988', 3],       // Floating point imprecision.
        ];
    }
    
    
    public function stringToExpectedFloatProvider()
    {
        return $this->swapDataSetColumns($this->floatToExpectedStringProvider(), [0 => 1]);
    }
    
    
    public function integerProvider()
    {
        return $this->extractDataSetColumn($this->integerToExpectedStringProvider(), 0);
    }
    
    
    public function integerToExpectedStringProvider()
    {
        return [
            [0, '0'],
            [-0, '0'],
            [1, '1'],
            [-1, '-1'],
            [123456789, '123456789'],
            [-123456789, '-123456789'],
        ];
    }
    
    
    public function stringToExpectedIntegerProvider()
    {
        return $this->swapDataSetColumns($this->integerToExpectedStringProvider(), [0 => 1]);
    }
    
    
    public function decimalToExpectedRatioProvider()
    {
        return [
            ['0.5', '1:2'],
            ['0.50000', '1:2'],
            ['1.50', '3:2'],
            ['1.5', '3:2'],
            ['0.5', '1:2'],
            ['1.2525', '501:400'],
            ['1.25', '5:4'],
            ['1.252500', '501:400'],
            ['0.5', '1:2'],
            ['0.50', '1:2'],
            ['0.75', '3:4'],
            ['1.33', '133:100'],
            ['1.33333333333333333333', '133333333333333333333:100000000000000000000'],
            [
                '1.3333333333333333333333333333333333333333',
                '13333333333333333333333333333333333333333:10000000000000000000000000000000000000000'
            ],
        ];
    }
}

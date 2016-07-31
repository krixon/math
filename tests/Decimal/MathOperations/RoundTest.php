<?php

namespace Krixon\Math\Test\Decimal\MathOperations;

use Krixon\Math\Decimal;
use Krixon\Math\Test\Decimal\DecimalTestCase;

/**
 * @coversDefaultClass Krixon\Math\Decimal
 * @covers ::<protected>
 * @covers ::<private>
 */
class RoundTest extends DecimalTestCase
{
    /**
     * @dataProvider roundProvider
     * @covers       ::round
     *
     * @param mixed  $decimal
     * @param int    $decimalPlaces
     * @param string $expected
     */
    public function testCanRound($decimal, int $decimalPlaces, string $expected)
    {
        $decimal = Decimal::create($decimal);
        $rounded = $decimal->round($decimalPlaces);
        
        self::assertSame($expected, $rounded->toString());
    }
    
    
    public function roundProvider() : array
    {
        return [
            [10, 0, '10'],
            ['10.05', 2, '10.05'],
            ['10.05', 1, '10.1'],
            ['10.05', 0, '10'],
            ['10.0036', 5, '10.0036'],
            ['10.0036', 4, '10.0036'],
            ['10.0036', 3, '10.004'],
            ['10.0036', 2, '10'],
            ['10.0036', 1, '10'],
        ];
    }
}

<?php

namespace Krixon\Math\Test\Decimal\MathOperations;

use Krixon\Math\Decimal;
use Krixon\Math\Test\Decimal\DecimalTestCase;

/**
 * @coversDefaultClass Krixon\Math\Decimal
 * @covers ::<protected>
 * @covers ::<private>
 */
class DivideByTest extends DecimalTestCase
{
    /**
     * @dataProvider divideByProvider
     * @covers       ::divideBy
     *
     * @param string $a
     * @param string $b
     * @param string $expected
     * @param int    $scale
     */
    public function testDivideBy(string $a, string $b, string $expected, $scale = null)
    {
        $a        = Decimal::fromString($a);
        $b        = Decimal::fromString($b);
        $expected = Decimal::fromString($expected);
        $result   = $a->divideBy($b, $scale);
        
        self::assertTrue($result->equals($expected), "$a ÷ $b != $expected (actual: $result)");
    }
    
    
    public function divideByProvider()
    {
        return [
            [1, 1, '1'],
            [1, 2, '0.5'],
            [10, 20, '0.5'],
            [9875412, 7821, '1262.67894131185270425776', 20],
            [9875412, 7821, '1262.678'],
        ];
    }
}

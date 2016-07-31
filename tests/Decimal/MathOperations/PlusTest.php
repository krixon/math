<?php

namespace Krixon\Math\Test\Decimal\MathOperations;

use Krixon\Math\Decimal;
use Krixon\Math\Test\Decimal\DecimalTestCase;

/**
 * @coversDefaultClass Krixon\Math\Decimal
 * @covers ::<protected>
 * @covers ::<private>
 */
class PlusTest extends DecimalTestCase
{
    /**
     * @dataProvider plusProvider
     * @covers       ::plus
     *
     * @param string $a
     * @param string $b
     * @param string $expected
     * @param int    $scale
     */
    public function testPlus(string $a, string $b, string $expected, $scale = Decimal::SCALE)
    {
        $a        = Decimal::fromString($a);
        $b        = Decimal::fromString($b);
        $expected = Decimal::fromString($expected);
        $result   = $a->plus($b, $scale);
        
        self::assertTrue($result->equals($expected), "$a + $b != $expected (actual: $result)");
    }
    
    
    public function plusProvider() : array
    {
        return [
            ['1', '1', '2'],
            ['10', '20', '30'],
            ['1', '0', '1'],
            ['0', '1', '1'],
            ['-1', '1', '0'],
            ['-1', '-1', '-2'],
            ['4323874085395586898689868986900219865', '1', '4323874085395586898689868986900219866'],
            [
                '4323874085395586898689868986900219865',
                '4323874085395586898689868986900219865',
                '8647748170791173797379737973800439730'
            ],
            // Default scale truncates the mantissa.
            [
                '9323874085395586898689868986900219865.9323874085395586898689868986900219865',
                '9323874085395586898689868986900219865.9323874085395586898689868986900219865',
                '18647748170791173797379737973800439731.86477481707911737973'
            ],
            // Custom scale preserves the mantissa and adds some significant digits.
            [
                '9323874085395586898689868986900219865.9323874085395586898689868986900219865',
                '9323874085395586898689868986900219865.9323874085395586898689868986900219865',
                '18647748170791173797379737973800439731.86477481707911737973797379738004397300000000000000',
                50
            ],
        ];
    }
}

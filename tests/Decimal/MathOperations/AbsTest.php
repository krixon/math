<?php

namespace Krixon\Math\Test\Decimal\MathOperations;

use Krixon\Math\Decimal;
use Krixon\Math\Test\Decimal\DecimalTestCase;

/**
 * @coversDefaultClass Krixon\Math\Decimal
 * @covers ::<protected>
 * @covers ::<private>
 */
class AbsTest extends DecimalTestCase
{
    /**
     * @dataProvider absProvider
     * @covers       ::abs
     *
     * @param mixed  $decimal
     * @param string $expected
     */
    public function testCanGetAbsoluteValue($decimal, string $expected)
    {
        $decimal  = Decimal::create($decimal);
        $absolute = $decimal->abs();
        
        self::assertSame($expected, $absolute->toString());
    }
    
    
    public function absProvider() : array
    {
        return [
            [10, '10'],
            [-10, '10'],
            ['-10.42', '10.42'],
            ['-10.42', '10.42'],
            ['4323874085395586898689868986900219865', '4323874085395586898689868986900219865'],
            ['4323874085395586898689868986900219865.1009882777', '4323874085395586898689868986900219865.1009882777'],
            ['-4323874085395586898689868986900219865', '4323874085395586898689868986900219865'],
            ['-4323874085395586898689868986900219865.1009882777', '4323874085395586898689868986900219865.1009882777'],
            [0, '0'],
            ['0.0', '0.0'],
        ];
    }
}

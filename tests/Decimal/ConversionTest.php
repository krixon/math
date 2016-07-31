<?php

namespace Krixon\Math\Test\Decimal;

use Krixon\Math\Decimal;
use Krixon\Math\Ratio;

/**
 * @coversDefaultClass Krixon\Math\Decimal
 * @covers ::<protected>
 * @covers ::<private>
 */
class ConversionTest extends DecimalTestCase
{
    /**
     * @dataProvider stringToExpectedStringProvider
     * @covers ::toString
     * @covers ::__toString
     *
     * @param string $decimal
     * @param string $expected
     * @param int    $scale
     */
    public function testCanConvertToString(string $decimal, string $expected, int $scale = null)
    {
        $decimal = Decimal::fromString($decimal);
        
        self::assertSame($expected, $decimal->toString($scale));
        
        if (null === $scale) {
            self::assertSame($expected, (string)$decimal);
        }
    }
    
    
    /**
     * @dataProvider decimalToExpectedRatioProvider
     * @covers ::toRatio
     *
     * @param string $decimal
     * @param string $expected
     */
    public function testCanConvertToRatio(string $decimal, string $expected)
    {
        $decimal = Decimal::fromString($decimal);
        $ratio   = $decimal->toRatio();
        
        self::assertInstanceOf(Ratio::class, $ratio);
        self::assertSame($expected, $ratio->toString());
    }
}

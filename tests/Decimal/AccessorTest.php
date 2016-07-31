<?php

namespace Krixon\Math\Test\Decimal;

use Krixon\Math\Decimal;

/**
 * @coversDefaultClass Krixon\Math\Decimal
 * @covers ::<protected>
 * @covers ::<private>
 */
class AccessorTest extends DecimalTestCase
{
    /**
     * @covers ::characteristic
     */
    public function testCanGetCharacteristic()
    {
        $decimal = Decimal::fromString('10.42');
        
        self::assertSame('10', $decimal->characteristic());
    }
    
    
    /**
     * @covers ::mantissa
     */
    public function testCanGetMantissa()
    {
        $decimal = Decimal::fromString('10.42');
        
        self::assertSame('42', $decimal->mantissa());
    }
    
    
    /**
     * @covers ::numberOfDecimalPlaces
     */
    public function testCanGetNumberOfDecimalPlaces()
    {
        $value = '42.0';
        
        for ($i = 1; $i < 256; $i++) {
            self::assertSame($i, Decimal::fromString($value)->numberOfDecimalPlaces());
            $value .= '0';
        }
    }
    
    
    /**
     * @dataProvider significantDigitsProvider
     * @covers ::numberOfSignificantDigits
     *
     * @param string $input
     * @param int    $expected
     */
    public function testCanGetNumberOfSignificantDigits(string $input, int $expected)
    {
        self::assertSame($expected, Decimal::fromString($input)->numberOfSignificantDigits());
    }
    
    
    public function significantDigitsProvider()
    {
        return [
            ['8', 1],
            ['3.14159', 6],
            ['42.123', 5],
            ['0.046', 2],
            ['4009', 4],
            ['7.90', 3],
            ['7.9000000', 8],
            ['7.90000000000000001', 18],
        ];
    }
}

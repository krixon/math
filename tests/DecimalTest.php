<?php

namespace Krixon\Math\Test;

use Krixon\Math\Decimal;

class DecimalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Krixon\Math\Decimal::__construct
     */
    public function testCanInstantiate()
    {
        $decimal = Decimal::fromString('42.25');
        
        self::assertInstanceOf(Decimal::class, $decimal);
    }
    
    
    /**
     * @dataProvider invalidStringProvider
     * @covers Krixon\Math\Decimal::fromString
     *
     * @param string $string
     */
    public function testCannotCreateFromInvalidString($string)
    {
        $this->expectException(\InvalidArgumentException::class);
        
        Decimal::fromString($string);
    }
    
    
    public function invalidStringProvider()
    {
        return [
            ['a.b'],
            ['12a.10'],
        ];
    }
    
    
    /**
     * @dataProvider validStringProvider
     * @covers Krixon\Math\Decimal::toString
     *
     * @param string $decimal
     * @param string $expected
     */
    public function testCanConvertToString(string $decimal, string $expected)
    {
        $decimal = Decimal::fromString($decimal);
        
        self::assertSame($expected, $decimal->toString());
    }
    
    
    public function validStringProvider() : array
    {
        return [
            ['.1', '0.1'],
            ['0.1', '0.1'],
            ['000.1', '0.1'],
            ['42.42', '42.42'],
            ['42', '42'],
            ['42.00', '42.00'],
        ];
    }
    
    
    /**
     * @covers Krixon\Math\Decimal::characteristic
     */
    public function testCanGetCharacteristic()
    {
        $decimal = Decimal::fromString('10.42');
        
        self::assertSame('10', $decimal->characteristic());
    }
    
    
    /**
     * @covers Krixon\Math\Decimal::mantissa
     */
    public function testCanGetMantissa()
    {
        $decimal = Decimal::fromString('10.42');
        
        self::assertSame('42', $decimal->mantissa());
    }
    
    
    /**
     * @dataProvider determineIfIntegerProvider
     * @covers       Krixon\Math\Decimal::isInteger
     *
     * @param string $decimal
     * @param bool   $expected
     */
    public function testCanDetermineIfInteger(string $decimal, bool $expected)
    {
        $decimal = Decimal::fromString($decimal);
        
        self::assertSame($expected, $decimal->isInteger());
    }
    
    
    public function determineIfIntegerProvider() : array
    {
        return [
            ['10', true],
            ['10.0', false],
            ['10.42', false],
        ];
    }
    
    
    /**
     * @dataProvider roundProvider
     * @covers       Krixon\Math\Decimal::round
     *
     * @param string $decimal
     * @param string $decimalPlaces
     * @param string $expected
     */
    public function testCanRound(string $decimal, string $decimalPlaces, string $expected)
    {
        $decimal = Decimal::fromString($decimal);
        $rounded = $decimal->round($decimalPlaces);
        
        self::assertSame($expected, $rounded->toString());
    }
    
    
    public function roundProvider() : array
    {
        return [
            ['10', 0, '10'],
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

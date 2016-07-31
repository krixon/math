<?php

namespace Krixon\Math\Test\Decimal;

use Krixon\Math\Decimal;
use Krixon\Math\Ratio;

/**
 * @coversDefaultClass Krixon\Math\Decimal
 * @covers ::<protected>
 * @covers ::<private>
 */
class InstantiationTest extends DecimalTestCase
{
    /**
     * @dataProvider invalidStringProvider
     * @covers ::__construct
     *
     * @param string $string
     */
    public function testCannotCreateFromInvalidString($string)
    {
        $this->expectException(\InvalidArgumentException::class);
        
        new Decimal($string);
    }
    
    
    public function invalidStringProvider() : array
    {
        return [
            ['a.b'],
            ['12a.10'],
        ];
    }
    
    
    /**
     * @dataProvider integerToExpectedStringProvider
     * @covers ::fromInteger
     *
     * @param int    $decimal
     * @param string $expected
     */
    public function testCreateFromIntegerStaticFactory(int $decimal, string $expected)
    {
        self::assertSame($expected, Decimal::fromInteger($decimal)->toString());
    }
    
    
    /**
     * @dataProvider integerProvider
     * @covers ::create
     *
     * @param int $decimal
     */
    public function testCreateDelegatesToStaticIntegerFactory(int $decimal)
    {
        self::assertTrue(Decimal::create($decimal)->equals(Decimal::fromInteger($decimal)));
    }
    
    
    /**
     * @dataProvider floatToExpectedStringProvider
     * @covers ::fromFloat
     *
     * @param float  $decimal
     * @param string $expected
     * @param int    $scale
     */
    public function testCreateFromFloatStaticFactory(float $decimal, string $expected, int $scale = null)
    {
        self::assertSame($expected, Decimal::fromFloat($decimal, $scale)->toString());
    }
    
    
    /**
     * @dataProvider floatProvider
     * @covers ::create
     *
     * @param float $decimal
     */
    public function testCreateDelegatesToStaticFloatFactory(float $decimal)
    {
        self::assertTrue(Decimal::create($decimal)->equals(Decimal::fromFloat($decimal)));
    }
    
    
    /**
     * @dataProvider stringToExpectedStringProvider
     * @covers ::fromString
     * @covers ::__construct
     *
     * @param string $decimal
     * @param string $expected
     * @param int    $scale
     */
    public function testCreateFromStringStaticFactory(string $decimal, string $expected, int $scale = null)
    {
        self::assertSame($expected, Decimal::fromString($decimal, $scale)->toString());
    }
    
    
    /**
     * @dataProvider stringProvider
     * @covers ::create
     *
     * @param string $decimal
     */
    public function testCreateDelegatesToStaticStringFactory(string $decimal)
    {
        self::assertTrue(Decimal::create($decimal)->equals(Decimal::fromString($decimal)));
    }
    
    
    /**
     * @dataProvider stringToExpectedStringProvider
     * @covers ::fromDecimal
     *
     * @param string $decimal
     * @param string $expected
     * @param int    $scale
     */
    public function testCreateFromDecimalStaticFactory(string $decimal, string $expected, int $scale = null)
    {
        $decimal = Decimal::create($decimal);
        
        self::assertSame($expected, Decimal::fromDecimal($decimal, $scale)->toString());
    }
    
    
    /**
     * @dataProvider stringProvider
     * @covers ::create
     *
     * @param string $decimal
     */
    public function testCreateDelegatesToStaticDecimalFactory(string $decimal)
    {
        self::assertTrue(Decimal::create($decimal)->equals(Decimal::fromDecimal(Decimal::create($decimal))));
    }
    
    
    /**
     * @dataProvider ratioToExpectedDecimalProvider
     * @covers ::fromRatio
     *
     * @param string $ratio
     * @param string $expected
     * @param int    $scale
     */
    public function testCreateFromRatioStaticFactory(string $ratio, string $expected, int $scale = null)
    {
        $ratio = Ratio::fromString($ratio);
        
        self::assertSame($expected, Decimal::fromRatio($ratio, $scale)->toString());
    }
    
    
    /**
     * @dataProvider ratioToExpectedDecimalProvider
     * @covers ::create
     *
     * @param string $ratio
     * @param string $decimal
     */
    public function testCreateDelegatesToStaticRatioFactory(string $ratio, string $decimal, int $scale = null)
    {
        $decimal = Decimal::create($decimal, $scale);
        $ratio   = Ratio::fromString($ratio);
        
        self::assertTrue($decimal->equals(Decimal::fromRatio($ratio, $scale)));
    }
    
    
    /**
     * @return array
     */
    public function ratioToExpectedDecimalProvider() : array
    {
        return [
            ['0.5:1', '0.5', 1],
            ['0.5:1', '0.5', null],
            ['0.5:1', '0.50000', 5],
            ['3:2', '1.50', 2],
            ['3:2', '1.5', null],
            ['1:2', '0.5', null],
            ['501:400', '1.2525', null],
            ['501:400', '1.25', 2],
            ['501:400', '1.252500', 6],
            ['1:2', '0.5', 1],
            ['1:2', '0.50', 2],
            ['3:4', '0.75', 2],
            ['4:3', '1.33', 2],
            ['4:3', '1.33333333333333333333', 20],
            ['4:3', '1.3333333333333333333333333333333333333333', 40],
        ];
    }
    
    
    /**
     * @covers ::zero
     */
    public function testCanCreateZero()
    {
        $zero = Decimal::zero();
        
        self::assertSame('0', $zero->toString());
        self::assertSame($zero, Decimal::zero());
    }
    
    
    /**
     * @covers ::one
     */
    public function testCanCreateOne()
    {
        $one = Decimal::one();
        
        self::assertSame('1', $one->toString());
        self::assertSame($one, Decimal::one());
    }
}

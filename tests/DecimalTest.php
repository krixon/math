<?php

namespace Krixon\Math\Test;

use Krixon\Math\Decimal;
use Krixon\Math\Ratio;

/**
 * @coversDefaultClass Krixon\Math\Decimal
 * @covers ::<protected>
 * @covers ::<private>
 */
class DecimalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validStringProvider
     * @covers ::__construct
     * @covers ::fromString
     *
     * @param string $decimal
     * @param string $expected
     * @param int    $scale
     */
    public function testCanCreateFromString(string $decimal, string $expected, int $scale = null)
    {
        $decimal = Decimal::fromString($decimal);
        
        self::assertSame($expected, $decimal->toString($scale));
    }
    
    
    /**
     * @dataProvider invalidStringProvider
     * @covers ::__construct
     * @covers ::fromString
     *
     * @param string $string
     */
    public function testCannotCreateFromInvalidString($string)
    {
        $this->expectException(\InvalidArgumentException::class);
        
        Decimal::fromString($string);
    }
    
    
    public function invalidStringProvider() : array
    {
        return [
            ['a.b'],
            ['12a.10'],
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
    
    
    /**
     * @dataProvider validStringProvider
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
    
    
    public function validStringProvider() : array
    {
        return [
            ['-1', '-1'],
            ['-0.1', '-0.1'],
            ['-0', '0'],
            ['-0.0', '0.0'],
            ['-0.05', '-0.05'],
            ['.1', '0.1'],
            ['0.1', '0.1'],
            ['000.1', '0.1'],
            ['42.42', '42.42'],
            ['42', '42'],
            ['42.00', '42.00'],
            ['42.12345', '42.123', 3],
            ['1', '1.000', 3],
            ['1', '1.0', 1],
            ['1', '1', 0],
            ['1.42', '1', 0],
            ['1.42', '1.4', 1],
        ];
    }
    
    
    /**
     * @dataProvider decimalToRatioProvider
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
    
    
    public function decimalToRatioProvider()
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
     * @dataProvider comparisonProvider
     * @covers ::compare
     *
     * @param string $a
     * @param string $b
     * @param int    $expected
     * @param int    $scale
     */
    public function testCanCompare(string $a, string $b, int $expected, int $scale = null)
    {
        $decimalA = Decimal::fromString($a);
    
        // This is to ensure we test the path where the same instance is used for comparison. In that case there
        // is an optimisation which returns 0 without any further checking. This is really an implementation detail
        // that cannot be explicitly tested, but this allows full coverage.
    
        $decimalB = $a === $b ? $decimalA : Decimal::fromString($b);
    
        $result = $decimalA->compare($decimalB, $scale);
    
        // Integer is not compared directly Decimal Ratio::compare() only guarantees a return of <=> 0.
    
        if ($expected === -1) {
            self::assertLessThan(0, $result);
        } elseif ($expected === 1) {
            self::assertGreaterThan(0, $result);
        } else {
            self::assertSame(0, $result);
        }
    }
    
    
    public function comparisonProvider()
    {
        return [
            ['1', '1', 0],
            ['1', '1.0', 0],
            ['0', '-0.0', 0],
            ['1', '1.000', 0],
            ['0', '-0.000', 0],
            ['1', '2', -1],
            ['1', '1.1', -1],
            ['1', '1.000000000000000000000000000000000000000000000000000000000000000000000000000001', -1],
            ['-2', '-1', -1],
            ['3', '1', 1],
            ['1', '-3', 1],
            ['1.00001', '1.00002', 0, 1], // The same at scale = 4.
            ['1.00001', '1.00002', -1, 5], // Different at scale = 5.
        ];
    }
    
    
    /**
     * @dataProvider validStringProvider
     * @covers ::equals
     *
     * @param string $string
     */
    public function testCanDetermineEquality(string $string)
    {
        $decimal  = Decimal::fromString($string);
        $subclass = new class($string) extends Decimal
        {
            // Why PSR-2, why?!
        };
        
        self::assertTrue(
            $decimal->equals($decimal),
            'Decimal does not equal itself'
        );
        
        self::assertTrue(
            $decimal->equals(clone $decimal),
            'Decimal does not equal clone of itself'
        );
        
        self::assertTrue(
            $decimal->equals(Decimal::fromString($string)),
            'Decimal does not equal equivalent Decimal'
        );
        
        self::assertTrue(
            $decimal->equals($decimal->divideBy(Decimal::one())),
            'Decimal does not equal itself div 1'
        );
        
        self::assertFalse(
            $decimal->equals($subclass),
            'Decimal incorrectly equals subclass'
        );
        
        self::assertFalse(
            $decimal->equals($decimal->plus(Decimal::fromString('2'))),
            "Decimal '$decimal' incorrectly equals itself plus 2"
        );
    }
    
    
    /**
     * @dataProvider determineIfIntegerProvider
     * @covers       ::isInteger
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
     * @dataProvider determineIfNegativeProvider
     * @covers       ::isNegative
     *
     * @param string $decimal
     * @param bool   $expected
     */
    public function testCanDetermineIfNegative(string $decimal, bool $expected)
    {
        $decimal = Decimal::fromString($decimal);
        
        self::assertSame($expected, $decimal->isNegative());
    }
    
    
    public function determineIfNegativeProvider() : array
    {
        return [
            ['-10', true],
            ['10', false],
            ['10.0', false],
            ['-10.0', true],
            ['10.42', false],
            ['-10.42', true],
            ['0', false],
            ['-0.0000000000000000000000000000000000000000000000000000000000000000000000000000001', true],
        ];
    }
    
    
    /**
     * @dataProvider determineIfPositiveProvider
     * @covers       ::isPositive
     *
     * @param string $decimal
     * @param bool   $expected
     */
    public function testCanDetermineIfPositive(string $decimal, bool $expected)
    {
        $decimal = Decimal::fromString($decimal);
        
        self::assertSame($expected, $decimal->isPositive());
    }
    
    
    public function determineIfPositiveProvider() : array
    {
        return [
            ['-10', false],
            ['10', true],
            ['10.0', true],
            ['-10.0', false],
            ['10.42', true],
            ['-10.42', false],
            ['0', false],
            ['-0.0000000000000000000000000000000000000000000000000000000000000000000000000000001', false],
            ['0.0000000000000000000000000000000000000000000000000000000000000000000000000000001', true],
        ];
    }
    
    
    /**
     * @dataProvider determineIfZeroProvider
     * @covers       ::isZero
     *
     * @param string $decimal
     * @param bool   $expected
     */
    public function testCanDetermineIfZero(string $decimal, bool $expected)
    {
        $decimal = Decimal::fromString($decimal);
        
        self::assertSame($expected, $decimal->isZero());
    }
    
    
    public function determineIfZeroProvider() : array
    {
        return [
            ['-10', false],
            ['10', false],
            ['10.0', false],
            ['-10.0', false],
            ['10.42', false],
            ['-10.42', false],
            ['-0', true],
            ['0', true],
            ['0.0000000000000000000000000000000000000000000000000000000000000000000000000000000', true],
            ['-0.0000000000000000000000000000000000000000000000000000000000000000000000000000000', true],
        ];
    }
    
    /**
     * @dataProvider determineIfOneProvider
     * @covers       ::isOne
     *
     * @param string $decimal
     * @param bool   $expected
     */
    public function testCanDetermineIfOne(string $decimal, bool $expected)
    {
        $decimal = Decimal::fromString($decimal);
        
        self::assertSame($expected, $decimal->isOne());
    }
    
    
    public function determineIfOneProvider() : array
    {
        return [
            ['-10', false],
            ['10', false],
            ['10.0', false],
            ['-10.0', false],
            ['10.42', false],
            ['-10.42', false],
            ['-1', false],
            ['-1.0', false],
            ['1', true],
            ['1.0', true],
            ['01.0', true],
            ['0000000001.0', true],
            ['1.0000000000000000000000000000000000000000000000000000000000000000000000000000000', true],
        ];
    }
    
    
    /**
     * @covers ::countDecimalPlaces
     */
    public function testCanCountDecimalPlaces()
    {
        $value = '42.0';
        
        for ($i = 1; $i < 256; $i++) {
            self::assertSame($i, Decimal::fromString($value)->countDecimalPlaces());
            $value .= '0';
        }
    }
    
    
    /**
     * @dataProvider roundProvider
     * @covers       ::round
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
    
    
    /**
     * @dataProvider absProvider
     * @covers       ::abs
     *
     * @param string $decimal
     * @param string $expected
     */
    public function testCanGetAbsoluteValue(string $decimal, string $expected)
    {
        $decimal  = Decimal::fromString($decimal);
        $absolute = $decimal->abs();
    
        self::assertSame($expected, $absolute->toString());
    }
    
    
    public function absProvider() : array
    {
        return [
            ['10', '10'],
            ['-10', '10'],
            ['-10.42', '10.42'],
            ['-10.42', '10.42'],
            ['4323874085395586898689868986900219865', '4323874085395586898689868986900219865'],
            ['4323874085395586898689868986900219865.1009882777', '4323874085395586898689868986900219865.1009882777'],
            ['-4323874085395586898689868986900219865', '4323874085395586898689868986900219865'],
            ['-4323874085395586898689868986900219865.1009882777', '4323874085395586898689868986900219865.1009882777'],
            ['0', '0'],
            ['0.0', '0.0'],
        ];
    }
    
    
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
    
    
    /**
     * @dataProvider divideByProvider
     * @covers       ::divideBy
     *
     * @param string $a
     * @param string $b
     * @param string $expected
     * @param int    $scale
     */
    public function testDivideBy(string $a, string $b, string $expected, $scale = Decimal::SCALE)
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
            [9875412, 7821, '1262.67894131185270425776'],
        ];
    }
}

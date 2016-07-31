<?php

namespace Krixon\Math\Test\Decimal;

use Krixon\Math\Decimal;

/**
 * @coversDefaultClass Krixon\Math\Decimal
 * @covers ::<protected>
 * @covers ::<private>
 */
class ComparisonTest extends DecimalTestCase
{
    /**
     * @dataProvider stringProvider
     * @covers ::equals
     *
     * @param string $string
     */
    public function testCanDetermineEquality(string $string)
    {
        $decimal  = Decimal::create($string);
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
     * @dataProvider comparisonProvider
     * @covers ::compare
     *
     * @param mixed  $a
     * @param mixed  $b
     * @param int    $expected
     * @param int    $scale
     */
    public function testCanCompare($a, $b, int $expected, int $scale = null)
    {
        $decimalA = Decimal::create($a);
        
        // This is to ensure we test the path where the same instance is used for comparison. In that case there
        // is an optimisation which returns 0 without any further checking. This is really an implementation detail
        // that cannot be explicitly tested, but this allows full coverage.
        
        $decimalB = $a === $b ? $decimalA : Decimal::create($b);
        
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
            [1, 1, 0],
            [1, '1.0', 0],
            [0, '-0.0', 0],
            [1, '1.000', 0],
            [0, '-0.000', 0],
            [1, 2, -1],
            [1, '1.1', -1],
            [1, '1.000000000000000000000000000000000000000000000000000000000000000000000000000001', -1],
            [-2, -1, -1],
            [3, 1, 1],
            [1, -3, 1],
            ['1.00001', '1.00002', 0, 1], // The same at scale = 4.
            ['1.00001', '1.00002', -1, 5], // Different at scale = 5.
        ];
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
}

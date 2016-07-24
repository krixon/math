<?php

namespace Krixon\Math\Test;

use Krixon\Math\Decimal;
use Krixon\Math\Fraction;
use Krixon\Math\Ratio;

class RatioTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider invalidStringProvider
     *
     * @param string $string
     */
    public function testCannotInstantiateFromInvalidString(string $string)
    {
        $this->expectException(\InvalidArgumentException::class);
        
        Ratio::fromString($string);
    }
    
    
    /**
     * @return array
     */
    public function invalidStringProvider() : array
    {
        return [
            ['a:b'],
            ['10:a'],
            ['a:10'],
            ['10a:10'],
            ['10:10a'],
            ['10a:10a'],
        ];
    }
    
    
    /**
     * @dataProvider validStringProvider
     *
     * @param string $string
     * @param string $antecedent
     * @param string $consequent
     */
    public function testCanInstantiateFromString(string $string, string $antecedent, string $consequent)
    {
        $ratio = Ratio::fromString($string);
        
        self::assertSame($antecedent, $ratio->antecedent());
        self::assertSame($consequent, $ratio->consequent());
    }
    
    
    /**
     * @return array
     */
    public function validStringProvider() : array
    {
        return [
            ['1:1',         '1',     '1'],
            ['1:2',         '1',     '2'],
            ['1:4',         '1',     '4'],
            ['1:25',        '1',     '25'],
            ['1:500000',    '1',     '500000'],
            ['10:1',        '10',    '1'],
            ['10:2',        '10',    '2'],
            ['10:10',       '10',    '10'],
            ['99999:66666', '99999', '66666'],
            ['1.25:1',      '1.25',  '1'],
            ['1.25:1.5991', '1.25',  '1.5991'],
        ];
    }
    
    
    /**
     * @dataProvider validDecimalStringInputProvider
     *
     * @param string $string
     * @param string $expected
     */
    public function testCanInstantiateFromDecimalString(string $string, string $expected)
    {
        $ratio = Ratio::fromDecimalString($string);
    
        self::assertSame($expected, $ratio->toString());
    }
    
    
    /**
     * @return array
     */
    public function validDecimalStringInputProvider() : array
    {
        return [
            ['.5',  '0.5:1'],
            ['0.5', '0.5:1'],
        ];
    }
    
    
    /**
     * @dataProvider validDecimalStringOutputProvider
     *
     * @param string   $ratio
     * @param string   $expected
     * @param int|null $scale
     */
    public function testCanConvertToDecimalString(string $ratio, string $expected, int $scale = null)
    {
        $ratio = Ratio::fromString($ratio);
    
        self::assertSame($expected, $ratio->toDecimalString($scale));
    }
    
    
    /**
     * @dataProvider validDecimalStringOutputProvider
     * @covers Krixon\Math\Ratio::toDecimal
     *
     * @param string   $ratio
     * @param string   $expected
     * @param int|null $scale
     */
    public function testCanConvertToDecimal(string $ratio, string $expected, int $scale = null)
    {
        $ratio   = Ratio::fromString($ratio);
        $decimal = $ratio->toDecimal($scale);
        
        self::assertInstanceOf(Decimal::class, $decimal);
        self::assertSame($expected, $decimal->toString());
    }
    
    
    /**
     * @return array
     */
    public function validDecimalStringOutputProvider() : array
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
     * @dataProvider simplifiedProvider
     *
     * @param string $string
     * @param string $expected
     */
    public function testCanSimplify(string $string, string $expected)
    {
        $ratio = Ratio::fromString($string)->simplify();
        
        self::assertSame($expected, $ratio->toString());
    }
    
    
    /**
     * @return array
     */
    public function simplifiedProvider() : array
    {
        return [
            ['0.5:1',     '1:2'],
            ['1.5:2',     '3:4'],
            ['0.2:0.004', '50:1'],
            ['.07:1.4',   '1:20'],
            ['6.3:8.4',   '3:4'],
        ];
    }
    
    
    public function testCanDetermineIfRatioIsOne()
    {
        self::assertTrue(Ratio::fromString('1:1')->isOne());
    }
    
    
    /**
     * @dataProvider fractionConversionProvider
     *
     * @param $ratio
     * @param $numerator
     * @param $denominator
     */
    public function testCanConvertToFraction($ratio, $numerator, $denominator)
    {
        $ratio = Ratio::fromString($ratio);
        $fraction = $ratio->toFraction();
        
        self::assertInstanceOf(Fraction::class, $fraction);
        self::assertSame($numerator, $fraction->numerator());
        self::assertSame($denominator, $fraction->denominator());
    }
    
    
    /**
     * @return array
     */
    public function fractionConversionProvider() : array
    {
        return [
            ['1.5:2',     3, 4],
            ['0.2:0.004', 50, 1],
            ['.07:1.4',   1, 20],
            ['6.3:8.4',   3, 4],
        ];
    }
    
    
    public function testCannotConvertToFractionWhenConsequentIsZero()
    {
        $this->expectException(\LogicException::class);
        
        Ratio::fromString('10:0')->toFraction();
    }
    
    
    /**
     * @dataProvider inversionProvider
     *
     * @param string $ratio
     * @param string $expected
     */
    public function testCanInvert(string $ratio, string $expected)
    {
        $ratio = Ratio::fromString($ratio);
        
        self::assertSame($expected, $ratio->invert()->toString());
    }
    
    
    public function inversionProvider()
    {
        return [
            ['3:2', '2:3'],
            ['1:2', '2:1'],
            ['1:1', '1:1'],
        ];
    }
    
    
    /**
     * @dataProvider clearDecimalsProvider
     *
     * @param string $ratio
     * @param string $expected
     */
    public function testCanClearDecimals(string $ratio, string $expected)
    {
        $ratio = Ratio::fromString($ratio);
        
        self::assertSame($expected, $ratio->clearDecimals()->toString());
    }
    
    
    public function clearDecimalsProvider()
    {
        return [
            ['1.5:2', '15:20'],
            ['0.2:0.004', '200:4'],
            ['0.07:1.4', '7:140'],
            ['6.3:8.4', '63:84'],
        ];
    }
}

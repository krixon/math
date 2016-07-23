<?php

namespace Krixon\Math\Test;

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
     * @param string $ratio
     * @param string $expected
     * @param int    $scale
     */
    public function testCanConvertToDecimalString(string $ratio, string $expected, int $scale)
    {
        $ratio = Ratio::fromString($ratio);
    
        self::assertSame($expected, $ratio->toDecimalString($scale));
    }
    
    
    /**
     * @return array
     */
    public function validDecimalStringOutputProvider() : array
    {
        return [
            ['0.5:1', '0.5', 1],
            ['0.5:1', '0.50000', 5],
            ['3:2', '1.50', 2],
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
}

<?php

namespace Krixon\Math\Test;

use Krixon\Math\Fraction;

class FractionTest extends \PHPUnit_Framework_TestCase
{
    public function testCannotInstantiateWithZeroDenominator()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        new Fraction(10, 0);
    }
    
    
    /**
     * @dataProvider invalidStringProvider
     *
     * @param string $string
     */
    public function testCannotCreateFromInvalidString($string)
    {
        $this->expectException(\InvalidArgumentException::class);
        
        Fraction::fromString($string);
    }
    
    
    public function invalidStringProvider()
    {
        return [
            ['a/b'],
            ['12a/10'],
        ];
    }
    
    
    /**
     * @dataProvider greatestCommonDivisorProvider
     *
     * @param string $fraction
     * @param int    $expected
     */
    public function testCanCalculateGreatestCommonDivisor($fraction, $expected)
    {
        $fraction = Fraction::fromString($fraction);
        
        self::assertSame($expected, $fraction->greatestCommonDivisor());
    }
    
    
    public function greatestCommonDivisorProvider()
    {
        return [
            ['1/2', 1],
            ['18/78', 6],
        ];
    }
    
    
    /**
     * @dataProvider isSimplifiedProvider
     *
     * @param string $fraction
     * @param bool   $expected
     */
    public function testCanDetermineIfSimplified($fraction, bool $expected)
    {
        $fraction = Fraction::fromString($fraction);
    
        self::assertSame($expected, $fraction->isSimplified());
    }
    
    
    public function isSimplifiedProvider()
    {
        return [
            ['1/2', true],
            ['3/4', true],
            ['18/78', false],
        ];
    }
}

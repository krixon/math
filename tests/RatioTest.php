<?php

namespace Krixon\Math\Test;

use Krixon\Math\Decimal;
use Krixon\Math\Ratio;

/**
 * @coversDefaultClass Krixon\Math\Ratio
 * @covers ::<protected>
 * @covers ::<private>
 */
class RatioTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     */
    public function testCanInstantiate()
    {
        $ratio = new Ratio(Decimal::one(), Decimal::one());
        
        self::assertInstanceOf(Ratio::class, $ratio);
    }
    
    
    /**
     * @dataProvider validStringProvider
     * @covers ::fromString
     *
     * @param string $string
     * @param string $antecedent
     * @param string $consequent
     */
    public function testCanInstantiateFromString(string $string, string $antecedent, string $consequent)
    {
        $ratio      = Ratio::fromString($string);
        $antecedent = Decimal::fromString($antecedent);
        $consequent = Decimal::fromString($consequent);
        
        self::assertTrue($antecedent->equals($ratio->dividend()));
        self::assertTrue($consequent->equals($ratio->divisor()));
    }
    
    
    /**
     * @covers ::__construct
     */
    public function testCannotInstantiateWithZeroConsequent()
    {
        self::expectException(\InvalidArgumentException::class);
        
        new Ratio(Decimal::one(), Decimal::zero());
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
     * @dataProvider invalidStringProvider
     * @covers ::fromString
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
            ['10a10a'],
        ];
    }
    
    
    /**
     * @covers ::dividend
     */
    public function testCanAccessDividend()
    {
        $ratio = Ratio::fromString('1:1');
        
        self::assertTrue($ratio->dividend()->equals(Decimal::one()));
    }
    
    
    /**
     * @covers ::divisor
     */
    public function testCanAccessDivisor()
    {
        $ratio = Ratio::fromString('1:1');
        
        self::assertTrue($ratio->divisor()->equals(Decimal::one()));
    }
    
    
    /**
     * @dataProvider validDecimalProvider
     * @covers ::toDecimal
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
    public function validDecimalProvider() : array
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
     * @covers ::toString
     */
    public function testCanConvertToString()
    {
        $string = '12:52';
        $ratio  = Ratio::fromString($string);
        
        self::assertSame($string, $ratio->toString());
    }
    
    
    /**
     * @dataProvider comparisonProvider
     * @covers ::compare
     *
     * @param string $a
     * @param string $b
     * @param int    $expected
     */
    public function testCanCompare(string $a, string $b, int $expected)
    {
        $ratioA = Ratio::fromString($a);
        
        // This is to ensure we test the path where the same instance is used for comparison. In that case there
        // is an optimisation which returns 0 without any further checking. This is really an implementation detail
        // that cannot be explicitly tested, but this allows full coverage.
        
        $ratioB = $a === $b ? $ratioA : Ratio::fromString($b);
        
        $result = $ratioA->compare($ratioB);
        
        // Integer is not compared directly because Ratio::compare() only guarantees a return of <=> 0.
        
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
            ['2:1', '2:1', 0],
            ['2:1', '4:2', 0],
            ['2:1', '3:1', -1],
            ['2:1', '1:1', 1],
        ];
    }
    
    
    /**
     * @dataProvider simplifiedProvider
     * @covers ::simplify
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
            ['1.33:1',   '133:100'],
        ];
    }
    
    
    /**
     * @covers ::simplify
     */
    public function testSimplifyingAlreadySimplifiedRatioRecyclesInstance()
    {
        $ratio = Ratio::fromString('2:1');
        
        self::assertSame($ratio, $ratio->simplify());
    }
    
    
    /**
     * @covers ::isOne
     */
    public function testCanDetermineIfRatioIsOne()
    {
        self::assertTrue(Ratio::fromString('1:1')->isOne());
    }
    
    
    /**
     * @dataProvider inversionProvider
     * @covers ::invert
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
     * @covers ::invert
     */
    public function testInvertingSymmetricalRatioRecyclesInstance()
    {
        $ratio = Ratio::fromString('2:2');
        
        self::assertSame($ratio, $ratio->invert());
    }
    
    
    /**
     * @dataProvider clearDecimalsProvider
     * @covers ::clearDecimals
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
            ['1:2', '1:2'],
            ['1.5:2', '15:20'],
            ['0.2:0.004', '200:4'],
            ['0.07:1.4', '7:140'],
            ['6.3:8.4', '63:84'],
        ];
    }
    
    
    /**
     * @dataProvider greatestCommonDivisorProvider
     * @covers ::greatestCommonDivisor
     *
     * @param string $ratio
     * @param int    $expected
     */
    public function testCanCalculateGreatestCommonDivisor($ratio, $expected)
    {
        $ratio    = Ratio::fromString($ratio);
        $expected = Decimal::fromString($expected);
        $result   = $ratio->greatestCommonDivisor();
        
        self::assertInstanceOf(Decimal::class, $result);
        
        self::assertTrue(
            $expected->equals($result),
            sprintf(
                'Decimal with value %s is not equal to expected Decimal with value %s',
                $result->toString(),
                $expected->toString()
            )
        );
    }
    
    
    public function greatestCommonDivisorProvider()
    {
        
        return [
            ['1:2', 1],
            ['18:78', 6],
            ['30315475:24440870', '31415'],
            ['37279462087332:366983722766', '564958'],
            ['4323874085395:586898689868986900219865', '85'],
            ['-4323874085395:586898689868986900219865', '85'],
        ];
    }
    
    
    /**
     * @covers ::greatestCommonDivisor
     */
    public function testGreatestCommonDivisorCalculationCachesResultBetweenCalls()
    {
        $ratio  = Ratio::fromString('18:78');
        $result = $ratio->greatestCommonDivisor();
        
        self::assertSame($result, $ratio->greatestCommonDivisor());
    }
    
    
    /**
     * @dataProvider isSimplifiedProvider
     * @covers ::isSimplified
     *
     * @param string $ratio
     * @param bool   $expected
     */
    public function testCanDetermineIfSimplified($ratio, bool $expected)
    {
        $ratio = Ratio::fromString($ratio);
        
        self::assertSame($expected, $ratio->isSimplified());
    }
    
    
    public function isSimplifiedProvider()
    {
        return [
            ['1:2', true],
            ['3:4', true],
            ['18:78', false],
        ];
    }
}

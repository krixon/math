<?php

namespace Krixon\Math;

/**
 * The ratio between two numbers.
 *
 * The two numbers can be integers or decimals. In order to support arbitrary precision, the numbers are expressed as
 * strings.
 */
class Ratio
{
    const SCALE = 20;
    
    /**
     * @var Decimal
     */
    private $dividend;
    
    /**
     * @var Decimal
     */
    private $divisor;
    
    /**
     * @var Decimal
     */
    private $decimalValue;
    
    /**
     * @var Decimal
     */
    private $gcd;
    
    
    /**
     * @param Decimal $dividend The ratio's dividend.
     * @param Decimal $divisor  The ratio's divisor.
     */
    public function __construct(Decimal $dividend, Decimal $divisor)
    {
        if ($divisor->isZero()) {
            throw new \InvalidArgumentException('Cannot create Ratio with zero consequent.');
        }
        
        $this->dividend = $dividend;
        $this->divisor  = $divisor;
    }
    
    
    /**
     * @param string $string
     *
     * @return Ratio
     */
    public static function fromString(string $string) : Ratio
    {
        // This check is intentionally loose since colon at position 0 is invalid.
        if (!strpos($string, ':')) {
            throw new \InvalidArgumentException("Ratio must be created with a string in the form 'A:B'.");
        }
        
        $parts = explode(':', trim($string), 2);
        
        try {
            $antecedent = Decimal::fromString($parts[0]);
            $consequent = Decimal::fromString($parts[1]);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException("Ratio must be created with a string in the form 'A:B'.", 0, $e);
        }
        
        return new static($antecedent, $consequent);
    }
    
    
    /**
     * The Ratio's dividend (digits before the colon).
     *
     * @return Decimal
     */
    public function dividend() : Decimal
    {
        return $this->dividend;
    }
    
    
    /**
     * The Ratio's divisor (digits after the colon).
     *
     * @return Decimal
     */
    public function divisor() : Decimal
    {
        return $this->divisor;
    }
    
    
    /**
     * Calculates the greatest common divisor of the ratio's dividend and divisor.
     *
     * @return Decimal
     */
    public function greatestCommonDivisor() : Decimal
    {
        if (null !== $this->gcd) {
            return $this->gcd;
        }
        
        $cleared = $this->clearDecimals();
        
        $a = $cleared->dividend->toString();
        $b = $cleared->divisor->toString();
        
        while (bccomp($b, '0') !== 0) {
            list ($a, $b) = [$b, bcmod($a, $b)];
        }
        
        $gcd = new Decimal($a);
        
        return $this->gcd = $gcd->abs();
    }
    
    
    /**
     * Determines if the ratio is exactly equal to 1.
     *
     * @return bool
     */
    public function isOne() : bool
    {
        return $this->compare(static::fromString('1:1')) === 0;
    }
    
    
    /**
     * Determines if the Fraction is simplified.
     *
     * @return bool
     */
    public function isSimplified() : bool
    {
        return $this->greatestCommonDivisor()->equals(Decimal::one());
    }
    
    
    /**
     * Compares the Ratio with another at the specified scale.
     *
     * @param Ratio $other
     * @param int   $scale
     *
     * @return int An integer less than, equal to or greater than 0 when this instance is respectively less than,
     *             equal to or greater than the $other instance.
     */
    public function compare(Ratio $other, int $scale = null) : int
    {
        if ($this === $other) {
            return 0;
        }
        
        return $this->toDecimal()->compare($other->toDecimal(), $scale);
    }
    
    
    /**
     * Simplifies the ratio by converting the antecedent and consequent to the smallest possible integers.
     *
     * @return Ratio
     */
    public function simplify() : Ratio
    {
        if ($this->isSimplified()) {
            return $this->clearDecimals();
        }
        
        $gcd      = $this->greatestCommonDivisor();
        $instance = clone $this;
        $instance = $instance->clearDecimals();
    
        $instance->dividend = $instance->dividend->divideBy($gcd, 0);
        $instance->divisor  = $instance->divisor->divideBy($gcd, 0);
    
        return $instance;
    }
    
    
    /**
     * Inverts the ratio by swapping the antecedent and consequent.
     *
     * @return Ratio
     */
    public function invert() : Ratio
    {
        if ($this->dividend->equals($this->divisor)) {
            return $this;
        }
        
        return new static($this->divisor, $this->dividend);
    }
    
    
    /**
     * @return Ratio
     */
    public function clearDecimals() : Ratio
    {
        if (!$this->containsDecimalPoint()) {
            return $this;
        }
        
        $decimalPlaces = max($this->dividend->countDecimalPlaces(), $this->divisor->countDecimalPlaces());
        $multiplier    = bcpow(10, $decimalPlaces);
        
        $antecedent = bcmul($this->dividend->toString(), $multiplier, 0);
        $consequent = bcmul($this->divisor->toString(), $multiplier, 0);
        
        return new static(new Decimal($antecedent), new Decimal($consequent));
    }
    
    
    /**
     * Converts the ratio to a Decimal of the specified scale.
     *
     * @param int|null $scale
     *
     * @return Decimal
     */
    public function toDecimal($scale = null) : Decimal
    {
        $antecedent = $this->dividend->toString();
        $consequent = $this->divisor->toString();
        
        if (null !== $scale) {
            return Decimal::fromString(bcdiv($antecedent, $consequent, $scale));
        }
        
        if (null !== $this->decimalValue) {
            return $this->decimalValue;
        }
        
        // No scale specified, calculate based on the default scale and then get rid of any extraneous zeros.
        // Note that usually these zeros would be considered significant, but the caller specifically requested
        // that we use the lowest possible precision decimal without losing any information from the ratio.
        
        $decimal = bcdiv($antecedent, $consequent, self::SCALE);
        $decimal = rtrim($decimal, '0');
        $decimal = rtrim($decimal, '.');
        
        return $this->decimalValue = Decimal::fromString($decimal);
    }
    
    
    /**
     * Converts the ratio to a native int.
     *
     * @return int
     */
    public function toInteger() : int
    {
        return (int)$this->toDecimal()->characteristic();
    }
    
    
    /**
     * Converts the ratio to a native float.
     *
     * This can result in loss of precision.
     *
     * @return float
     */
    public function toFloat() : float
    {
        return (float)$this->toDecimal()->toString();
    }
    
    
    /**
     * Converts the Ratio to a string.
     *
     * @return string
     */
    public function toString() : string
    {
        return $this->dividend->toString() . ':' . $this->divisor->toString();
    }
    
    
    /**
     * Determines if the ratio contains a decimal point in its antecedent or consequent.
     *
     * @return bool
     */
    private function containsDecimalPoint()
    {
        return strpos($this->toString(), '.') !== false;
    }
}

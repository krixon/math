<?php

namespace Krixon\Math;

class Ratio
{
    const SCALE = 20;
    
    private $antecedent;
    private $consequent;
    
    
    /**
     * @param string $antecedent The ratio's antecedent (value to the left of the colon).
     * @param string $consequent The ratio's consequent (value to the right of the colon).
     */
    public function __construct(string $antecedent, string $consequent)
    {
        $scale = self::maxDecimalPlaces($antecedent, $consequent);
        
        // Ensure both parts have consistent leading zeros (always 1).
        $this->antecedent = bcmul($antecedent, 1, $scale);
        $this->consequent = bcmul($consequent, 1, $scale);
    }
    
    
    /**
     * @param string $string
     *
     * @return Ratio
     */
    public static function fromString(string $string) : Ratio
    {
        if (!preg_match('/^(\d+|\d*(?:\.\d+)?):(\d+|\d*(?:\.\d+)?)?$/', $string, $matches)) {
            throw new \InvalidArgumentException("Ratio must be created with a string in the form 'A:B'.");
        }
        
        return new static($matches[1], $matches[2]);
    }
    
    
    /**
     * Creates a new instance from a decimal string.
     *
     * For example, given the string "0.5" this will create a new Ratio of "0.5:1". To get "1:2" instead, call
     * simplify() on the returned instance.
     *
     * @param string $string
     *
     * @return Ratio
     */
    public static function fromDecimalString(string $string) : Ratio
    {
        return static::fromString("$string:1");
    }
    
    
    /**
     * The Ratio's antecedent (digits before the colon).
     *
     * @return string
     */
    public function antecedent() : string
    {
        return $this->antecedent;
    }
    
    
    /**
     * The Ratio's consequent (digits after the colon).
     *
     * @return string
     */
    public function consequent() : string
    {
        return $this->consequent;
    }
    
    
    /**
     * Converts the Ratio to a Fraction.
     *
     * @return Fraction
     */
    public function toFraction() : Fraction
    {
        if (bccomp($this->consequent, 0, self::SCALE) === 0) {
            throw new \LogicException(
                'Cannot convert Ratio ' . $this->toString() . ' to a Fraction because its consequent is zero.'
            );
        }
        
        $cleared     = $this->clearDecimals();
        $numerator   = (int)$cleared->antecedent();
        $denominator = (int)$cleared->consequent();
        
        $fraction = new Fraction($numerator, $denominator);
        
        return $fraction->simplify();
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
        return Decimal::fromString($this->toDecimalString($scale));
    }
    
    
    /**
     * Converts the Ratio to a string.
     *
     * @return string
     */
    public function toString() : string
    {
        return $this->antecedent . ':' . $this->consequent;
    }
    
    
    /**
     * Converts the Ratio to a decimal string.
     *
     * For example, the ratio 2:3 at a scale of 2 will result in the string '1.50'. With no specified scale this will
     * result in a string of '1.5'.
     *
     * For decimals with long (or infinite) mantissas, the default scale (self::SCALE) will be used unless a higher
     * scale is specified.
     *
     * @param int|null $scale The number of digits after the decimal place. Note that this does not round. If no scale
     *                        is specified, the decimal string will be as short as possible whilst maintaining as
     *                        much information from the ratio as possible.
     *
     * @return string
     */
    public function toDecimalString(int $scale = null) : string
    {
        if (null !== $scale) {
            return bcdiv($this->antecedent, $this->consequent, $scale);
        }
    
        // No scale specified, calculate based on the default scale and then get rid of any extraneous zeros.
        // Note that usually these zeros would be considered significant, but the caller specifically requested
        // that we use the lowest possible precision decimal without losing any information from the ratio.
        
        $decimal = bcdiv($this->antecedent, $this->consequent, self::SCALE);
        
        return rtrim($decimal, '0');
    }
    
    
    /**
     * Simplifies the ratio by converting the antecedent and consequent to the smallest possible integers.
     *
     * @return Ratio
     */
    public function simplify() : Ratio
    {
        return $this->toFraction()->toRatio();
    }
    
    
    /**
     * Inverts the ratio by swapping the antecedent and consequent.
     *
     * @return Ratio
     */
    public function invert() : Ratio
    {
        if ($this->antecedent === $this->consequent) {
            return $this;
        }
        
        return new static($this->consequent, $this->antecedent);
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
     * @param Ratio $other
     *
     * @return int An integer less than, equal to or greater than 0 when this instance is respectively less than,
     *             equal to or greater than the $other instance.
     */
    public function compare(Ratio $other) : int
    {
        return bccomp($this->simplify()->toDecimalString(), $other->simplify()->toDecimalString(), self::SCALE);
    }
    
    
    /**
     * @return Ratio
     */
    public function clearDecimals() : Ratio
    {
        if (!$this->containsDecimal()) {
            return $this;
        }
        
        $decimalPlaces = self::maxDecimalPlaces($this->antecedent, $this->consequent);
        $multiplier    = 10 ** $decimalPlaces;
        
        $antecedent = bcmul($this->antecedent, $multiplier, 0);
        $consequent = bcmul($this->consequent, $multiplier, 0);
        
        return new static($antecedent, $consequent);
    }
    
    
    /**
     * Determines if the ratio contains a decimal point in its antecedent or consequent.
     *
     * @return bool
     */
    public function containsDecimal()
    {
        return strpos($this->toString(), '.') !== false;
    }
    
    
    /**
     * Calculates the maximum number of decimal places in the antecedent and consequent.
     *
     * @param string $antecedent
     * @param string $consequent
     *
     * @return int
     */
    private static function maxDecimalPlaces(string $antecedent, string $consequent) : int
    {
        $antecedent = Decimal::fromString($antecedent);
        $consequent = Decimal::fromString($consequent);
        
        return max($antecedent->countDecimalPlaces(), $consequent->countDecimalPlaces());
    }
}

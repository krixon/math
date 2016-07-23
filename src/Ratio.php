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
        // Use bcmul to get consistent leading zeros (always 1).
        $this->antecedent = bcmul($antecedent, 1, self::SCALE);
        $this->consequent = bcmul($consequent, 1, self::SCALE);
    }
    
    
    /**
     * @param string $string
     *
     * @return Ratio
     */
    public static function fromString(string $string) : Ratio
    {
        if (!preg_match('/^(?:\d+|\d*(\.\d+)?):(?:\d+|\d*(\.\d+)?)?$/', $string)) {
            throw new \InvalidArgumentException("Ratio must be created with a string in the form 'A:B'.");
        }
        
        $parts = explode(':', $string, 2);
        
        return new static($parts[0], $parts[1]);
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
        
        list($numerator, $denominator) = self::clearDecimals($this->antecedent, $this->consequent);
        
        $fraction = new Fraction((int)$numerator, (int)$denominator);
        
        return $fraction->simplify();
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
     * For example, the ratio 2:3 at a scale of 2 will result in the string '1.50'.
     *
     * @param int $scale The number of digits after the decimal place.
     *
     * @return string
     */
    public function toDecimalString(int $scale = self::SCALE) : string
    {
        return bcdiv($this->antecedent, $this->consequent, $scale);
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
     * Counts the number of significant digits in the value.
     *
     * @param $value
     *
     * @return int
     */
    private static function countSignificantDigits($value) : int
    {
        return strlen(substr(strrchr($value, '.'), 1));
    }
    
    
    /**
     * @param string[] $values
     *
     * @return array
     */
    private static function clearDecimals(string ...$values) : array
    {
        $significantDigits = self::maxSignificantDigits(...$values);
        $multiplier        = 10 ** $significantDigits;
        
        array_walk($values, function (string &$value) use ($multiplier) {
            $value = bcmul($value, $multiplier, self::SCALE);
        });
        
        return $values;
    }
    
    
    /**
     * Calculates the maximum number of significant digits from the set of values.
     *
     * @param string[] $values
     *
     * @return int
     */
    private static function maxSignificantDigits(string ...$values) : int
    {
        $significantDigits = 0;
        
        foreach ($values as $value) {
            $significantDigits = max($significantDigits, self::countSignificantDigits($value));
        }
        
        return $significantDigits;
    }
}

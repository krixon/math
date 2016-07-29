<?php

namespace Krixon\Math;

class Decimal
{
    /**
     * Default scale used when none is specified.
     */
    const SCALE = 20;
    
    /**
     * @var string
     */
    private $characteristic;
    
    /**
     * @var string|null
     */
    private $mantissa;
    
    /**
     * @var string
     */
    private $value;
    
    /**
     * @var Decimal
     */
    private static $zero;
    
    /**
     * @var Decimal
     */
    private static $one;
    
    
    
    /**
     * @param string $value
     */
    public function __construct(string $value)
    {
        if (!preg_match('#^(?<s>[+-])?(?<c>\d*)(?:\.(?<m>\d+))?$#', $value, $matches)) {
            throw new \InvalidArgumentException("Cannot create Decimal from invalid string '$value'.");
        }
        
        $sign           = $matches['s'] ?? '';
        $characteristic = $matches['c'] ?? '0';
        
        // Multiply by 1 to be sure of having consistent leading zeroes.
        $characteristic = bcmul($characteristic, 1);
        $mantissa       = $matches['m'] ?? null;
        
        // bccomp has some weird behaviour where it treats -0.0 as < 0. We don't want to distinguish between the two
        // as the difference is only relevant in things like floating point numbers.
        if ($sign === '-' && $characteristic === '0' && (null === $mantissa || bccomp($mantissa, '0') === 0)) {
            $sign = '';
        }
        
        // The sign is added separately because bcmul might lose it for number such as -0.1 due to the mantissa.
        $this->characteristic = $sign . $characteristic;
        $this->mantissa       = $mantissa;
        $this->value          = $this->characteristic . (null !== $mantissa ? ".$mantissa" : '');
    }
    
    
    /**
     * @return Decimal
     */
    public static function zero()
    {
        if (null === static::$zero) {
            static::$zero = new static('0');
        }
        
        return static::$zero;
    }
    
    
    /**
     * @return Decimal
     */
    public static function one()
    {
        if (null === static::$one) {
            static::$one = new static('1');
        }
        
        return static::$one;
    }
    
    
    /**
     * Creates a new instance from a decimal string.
     *
     * @param string $string
     *
     * @return static
     */
    public static function fromString(string $string)
    {
        return new static($string);
    }
    
    
    /**
     * The characteristic (integer component) of the decimal.
     *
     * @return string
     */
    public function characteristic() : string
    {
        return $this->characteristic;
    }
    
    
    /**
     * The mantissa (fractional component) of the decimal.
     *
     * @return string|null
     */
    public function mantissa()
    {
        return $this->mantissa;
    }
    
    
    /**
     * Determines if this instance is equal to another.
     *
     * @param Decimal $other
     * @param int     $scale
     *
     * @return bool
     */
    public function equals(Decimal $other, int $scale = null) : bool
    {
        if ($other === $this) {
            return true;
        }
        
        if (get_class($this) !== get_class($other)) {
            return false;
        }
        
        return $this->compare($other, $scale) === 0;
    }
    
    
    /**
     * Compares the Decimal with another at the specified scale.
     *
     * @param Decimal $other
     * @param int     $scale
     *
     * @return int An integer less than, equal to or greater than 0 when this instance is respectively less than,
     *             equal to or greater than the $other instance.
     */
    public function compare(Decimal $other, int $scale = null) : int
    {
        if ($this === $other) {
            return 0;
        }
        
        $scale = $this->resolveMinimumScale($scale, $other);
        
        return bccomp($this->toString(), $other->toString(), $scale);
    }
    
    
    /**
     * @return bool
     */
    public function isNegative() : bool
    {
        return $this->compare(static::zero()) < 0;
    }
    
    
    /**
     * @return bool
     */
    public function isZero() : bool
    {
        return $this->compare(static::zero()) === 0;
    }
    
    
    /**
     * @return bool
     */
    public function isOne() : bool
    {
        return $this->compare(static::one()) === 0;
    }
    
    
    /**
     * @return bool
     */
    public function isPositive() : bool
    {
        return $this->compare(static::zero()) > 0;
    }
    
    
    /**
     * Determines if the decimal is an integer.
     *
     * Note that values such as 10.0 are not considered integers because the .0 is a significant digit. A value is
     * only an integer if it does not have a mantissa at all.
     *
     * @return bool
     */
    public function isInteger() : bool
    {
        return null === $this->mantissa;
    }
    
    
    /**
     * Counts the number of decimal places.
     *
     * Note that this is not the same thing as significant digits. The number of decimal places is simply the number
     * of digits after the decimal point.
     *
     * @return int
     */
    public function countDecimalPlaces() : int
    {
        return strlen($this->mantissa);
    }
    
    
    /**
     * Rounds the decimal to the specified number of decimal places.
     *
     * @param int $decimalPlaces
     *
     * @return Decimal
     */
    public function round(int $decimalPlaces = 0) : Decimal
    {
        // FIXME: Support other rounding modes aside from just ROUND_HALF_UP.
        
        if ($decimalPlaces === $this->countDecimalPlaces()) {
            return $this;
        }
        
        $mantissa = str_repeat('0', $decimalPlaces) . '5';
        $value    = bcadd($this->toString(), "0.$mantissa", $decimalPlaces + 1);
        $value    = bcdiv($value, '1.0', $decimalPlaces);
        
        if (strpos($value, '.') !== false) {
            $value = rtrim($value, '0');
            $value = rtrim($value, '.');
        }
        
        return static::fromString($value);
    }
    
    
    /**
     * @return Decimal
     */
    public function abs() : Decimal
    {
        if ($this->isPositive()) {
            return $this;
        }
        
        return static::fromString(bcmul($this->value, '-1', $this->countDecimalPlaces()));
    }
    
    
    /**
     * Adds another decimal to this.
     *
     * @param Decimal  $other
     * @param int|null $scale
     *
     * @return Decimal
     */
    public function plus(Decimal $other, int $scale = self::SCALE) : Decimal
    {
        $result = bcadd($this->value, $other->value, $scale);
        
        return static::fromString($result);
    }
    
    
    /**
     * Divides this decimal by another.
     *
     * @param Decimal $other
     * @param int     $scale
     *
     * @return Decimal
     */
    public function divideBy(Decimal $other, int $scale = self::SCALE) : Decimal
    {
        if ($other->isOne()) {
            return $this;
        }
        
        $result = bcdiv($this->value, $other->value, $scale);
        
        return static::fromString($result);
    }
    
    
    /**
     * Converts the decimal to a string.
     *
     * @param int $scale
     *
     * @return string
     */
    public function toString(int $scale = null) : string
    {
        if (null !== $scale) {
            return bcmul($this->value, 1, $scale);
        }
        
        return $this->value;
    }
    
    
    /**
     * Converts the decimal to a string, with magic!
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->toString();
    }
    
    
    /**
     * Converts this decimal to a ratio.
     *
     * For example, given the decimal 1.5 this will return the ratio 2:1.
     *
     * @return Ratio
     */
    public function toRatio() : Ratio
    {
        $ratio = new Ratio($this, new Decimal('1'));
        
        return $ratio->simplify();
    }
    
    
    /**
     * @param int|null  $scale
     * @param Decimal[] ...$others
     *
     * @return int|mixed
     */
    private function resolveMinimumScale(int $scale = null, Decimal ...$others)
    {
        if (null !== $scale) {
            return $scale;
        }
        
        $others[] = $this;
        
        return max(array_map(function (Decimal $other) {
            return $other->countDecimalPlaces();
        }, $others));
    }
}

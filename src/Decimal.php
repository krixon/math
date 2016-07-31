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
     * @param int    $scale
     */
    public function __construct(string $value, int $scale = null)
    {
        if (!preg_match('#^(?<s>[+-])?(?<c>\d*)(?:\.(?<m>\d+))?$#', $value, $matches)) {
            throw new \InvalidArgumentException("Cannot create Decimal from invalid string '$value'.");
        }
        
        $sign           = $matches['s'] ?? '';
        $characteristic = $matches['c'] ?? '0';
        
        // Multiply by 1 to be sure of having consistent leading zeroes (just one).
        $characteristic = bcmul($characteristic, 1);
        $mantissa       = $matches['m'] ?? null;
        
        // bccomp has some weird behaviour where it treats -0.0 as < 0. We don't want to distinguish between the two
        // as the difference is only relevant in things like floating point numbers. If the value is 0 and a sign is
        // included, strip the sign out.
        if ($sign === '-' && $characteristic === '0' && (null === $mantissa || bccomp($mantissa, '0') === 0)) {
            $sign = '';
        } elseif ($sign === '+') {
            $sign = '';
        }
        
        if (null !== $scale) {
            $mantissa = self::scaleMantissa($mantissa, $scale);
        }
        
        // The sign is added separately because bcmul might lose it for number such as -0.1 due to the mantissa.
        $this->characteristic = $sign . $characteristic;
        $this->mantissa       = $mantissa;
        $this->value          = $this->characteristic . (null !== $mantissa ? ".$mantissa" : '');
    }
    
    
    /**
     * @return Decimal
     */
    public static function zero() : Decimal
    {
        if (null === static::$zero) {
            static::$zero = new static('0');
        }
        
        return static::$zero;
    }
    
    
    /**
     * @return Decimal
     */
    public static function one() : Decimal
    {
        if (null === static::$one) {
            static::$one = new static('1');
        }
        
        return static::$one;
    }
    
    
    /**
     * General purpose static factory for creating an instance from any supported type.
     *
     * @param mixed    $value
     * @param int|null $scale
     *
     * @return Decimal
     */
    public static function create($value, int $scale = null) : Decimal
    {
        if (is_int($value)) {
            return static::fromInteger($value);
        }
        
        if (is_float($value)) {
            return static::fromFloat($value, $scale);
        }
        
        if (is_string($value)) {
            return static::fromString($value, $scale);
        }
        
        if ($value instanceof Decimal) {
            return static::fromDecimal($value, $scale);
        }
        
        if ($value instanceof Ratio) {
            return static::fromRatio($value, $scale);
        }
        
        throw new \InvalidArgumentException('Decimal can only be created from an int, float or string.');
    }
    
    
    /**
     * @param int $value
     *
     * @return Decimal
     */
    public static function fromInteger(int $value) : Decimal
    {
        return new static((string)$value, 0);
    }
    
    
    /**
     * @param float $value
     * @param int   $scale
     *
     * @return Decimal
     */
    public static function fromFloat(float $value, int $scale = null) : Decimal
    {
        if (INF === $value || -INF === $value) {
            throw new \InvalidArgumentException('Support for infinite Decimals is not implemented.');
        }
        
        if (is_nan($value)) {
            throw new \InvalidArgumentException('Cannot create a Decimal from NaN.');
        }
        
        if (null === $scale) {
            $scale = self::SCALE;
        }
        
        $value = number_format($value, $scale, '.', '');
        
        return new static($value);
    }
    
    
    /**
     * Creates a new instance from a decimal string.
     *
     * @param string   $value
     * @param int|null $scale
     *
     * @return Decimal
     */
    public static function fromString(string $value, int $scale = null) : Decimal
    {
        return new static($value, $scale);
    }
    
    
    /**
     * Creates a new instance from an existing instance.
     *
     * Note that if the scale is not specified the original instance will be returned since it represents the same
     * value.
     *
     * @param Decimal  $decimal
     * @param int|null $scale
     *
     * @return Decimal
     */
    public static function fromDecimal(Decimal $decimal, int $scale = null) : Decimal
    {
        if (null === $scale) {
            return $decimal;
        }
        
        return $decimal->scale($scale);
    }
    
    
    /**
     * Creates a new instance from a Ratio.
     *
     * @param Ratio    $ratio
     * @param int|null $scale
     *
     * @return Decimal
     */
    public static function fromRatio(Ratio $ratio, int $scale = null) : Decimal
    {
        return $ratio->toDecimal($scale);
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
        
        if (null === $scale) {
            $scale = max($this->numberOfDecimalPlaces(), $other->numberOfDecimalPlaces());
        }
        
        return $this->compare($other, $scale) === 0;
    }
    
    
    /**
     * @param Decimal  $other
     * @param int|null $scale
     *
     * @return bool
     */
    public function isLessThan(Decimal $other, int $scale = null) : bool
    {
        return -1 === $this->compare($other, $scale);
    }
    
    
    /**
     * @param Decimal  $other
     * @param int|null $scale
     *
     * @return bool
     */
    public function isLessThanOrEqualTo(Decimal $other, int $scale = null) : bool
    {
        return $this->equals($other, $scale) || $this->isLessThan($other, $scale);
    }
    
    
    /**
     * @param Decimal  $other
     * @param int|null $scale
     *
     * @return bool
     */
    public function isGreaterThan(Decimal $other, int $scale = null) : bool
    {
        return 1 === $this->compare($other, $scale);
    }
    
    
    /**
     * @param Decimal  $other
     * @param int|null $scale
     *
     * @return bool
     */
    public function isGreaterThanOrEqualTo(Decimal $other, int $scale = null) : bool
    {
        return $this->equals($other, $scale) || $this->isGreaterThan($other, $scale);
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
    public function numberOfDecimalPlaces() : int
    {
        return strlen($this->mantissa);
    }
    
    
    /**
     * Counts the number of significant digits of $decimal.
     *
     * Zeros before other digits are not significant. 0.046 has 2 significant digits.
     *
     * Zeros between other digits are always significant. 4009 has 4 significant digits.
     *
     * Zeros behind other digits and a decimal point are always significant. 7.90 has 3 significant digits.
     *
     * It's technically impossible to tell if zeros at the end of a number but in front of the decimal point are
     * significant. 4200 could have 2, 3 or 4 significant digits. For the purposes of this calculation, they are
     * counted, so 4200 has 4 significant digits.
     *
     * @return int
     */
    public function numberOfSignificantDigits() : int
    {
        $characteristicDigits = strlen(ltrim($this->characteristic, '-0'));
        
        if (0 === $characteristicDigits) {
            $mantissaDigits = strlen(ltrim($this->mantissa, '0'));
        } else {
            $mantissaDigits = strlen($this->mantissa);
        }
        
        return $characteristicDigits + $mantissaDigits;
    }
    
    
    /**
     * Scales the decimal to the specified precision.
     *
     * @param int $scale
     *
     * @return Decimal
     */
    public function scale(int $scale) : Decimal
    {
        if ($scale === $this->numberOfDecimalPlaces()) {
            return $this;
        }
        
        return static::fromString($this->toString($scale));
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
        
        if ($decimalPlaces === $this->numberOfDecimalPlaces()) {
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
     * Returns a decimal with the absolute value of this decimal.
     *
     * @return Decimal
     */
    public function abs() : Decimal
    {
        if ($this->isPositive()) {
            return $this;
        }
        
        return static::fromString(bcmul($this->value, '-1', $this->numberOfDecimalPlaces()));
    }
    
    
    /**
     * Adds another decimal to this.
     *
     * @param Decimal  $addend
     * @param int|null $scale
     *
     * @return Decimal
     */
    public function plus(Decimal $addend, int $scale = null) : Decimal
    {
        if (null === $scale) {
            $scale = max($this->numberOfDecimalPlaces(), $addend->numberOfDecimalPlaces());
        }
        
        $result = bcadd($this->value, $addend->value, $scale);
    
        return new static($result);
    }
    
    
    /**
     * Subtracts another decimal from this.
     *
     * @param Decimal  $subtrahend
     * @param int|null $scale
     *
     * @return Decimal
     */
    public function minus(Decimal $subtrahend, int $scale = null) : Decimal
    {
        if (null === $scale) {
            $scale = max($this->numberOfDecimalPlaces(), $subtrahend->numberOfDecimalPlaces());
        }
        
        $result = bcsub($this->value, $subtrahend->value, $scale);
        
        return new static($result);
    }
    
    
    /**
     * Multiplies this decimal by another.
     *
     * @param Decimal $factor
     * @param null    $scale
     *
     * @return Decimal
     */
    public function multiplyBy(Decimal $factor, $scale = null) : Decimal
    {
        if ($factor->isZero()) {
            return static::zero();
        }
    
        if (null === $scale) {
            $scale = $this->numberOfDecimalPlaces() + $factor->numberOfDecimalPlaces();
        }
        
        $result = bcmul($this->value, $factor->value, $scale);
        
        return new static($result);
    }
    
    
    /**
     * Divides this decimal by another.
     *
     * @param Decimal $other
     * @param int     $scale
     *
     * @return Decimal
     */
    public function divideBy(Decimal $other, int $scale = null) : Decimal
    {
        if ($other->isZero()) {
            throw new \LogicException('Cannot divide by zero.');
        }
        
        if ($other->isOne()) {
            return $this;
        }
        
        if (null === $scale) {
            // Determine a reasonable scale to avoid too much loss of precision.
            
            $abs        = $this->abs();
            $otherAbs   = $other->abs();
            $otherLog10 = self::computeLog10($otherAbs->value, $otherAbs->numberOfDecimalPlaces(), 1);
            $log10      = self::computeLog10($abs->value, $abs->numberOfDecimalPlaces(), 1) - $otherLog10;
            
            $totalDecimalPlaces   = $this->numberOfDecimalPlaces() + $other->numberOfDecimalPlaces();
            $maxSignificantDigits = max($this->numberOfSignificantDigits(), $other->numberOfSignificantDigits());
            
            $scale = (int)max(
                $totalDecimalPlaces,
                $maxSignificantDigits - max(ceil($log10), 0),
                ceil(-$log10) + 1
            );
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
        if ($scale !== null) {
            $value    = $this->characteristic;
            $mantissa = self::scaleMantissa($this->mantissa, $scale);
            
            if (null !== $mantissa) {
                $value .= ".$mantissa";
            }
            
            return $value;
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
            return $other->numberOfDecimalPlaces();
        }, $others));
    }
    
    
    /**
     * Computes the base-10 logarithm of the specified value.
     *
     * @param string $value
     * @param int    $inputScale
     * @param int    $scale
     *
     * @return string
     */
    private static function computeLog10(string $value, int $inputScale, int $scale)
    {
        $length = strlen($value);
        
        switch (bccomp($value, '1', $inputScale)) {
            case 1:
                $approxLog10 = $length - ($inputScale > 0 ? ($inputScale + 2) : 1);
                
                return bcadd(
                    $approxLog10,
                    log10(bcdiv($value, bcpow('10', $approxLog10), min($length, $scale))),
                    $scale
                );
            case -1:
                preg_match('/^0*\.(0*)[1-9][0-9]*$/', $value, $matches);
                
                $approxLog10 = -strlen($matches[1]) - 1;
                
                return bcadd(
                    $approxLog10,
                    log10(bcmul($value, bcpow('10', -$approxLog10), $inputScale + $approxLog10)),
                    $scale
                );
        }
        
        return '0';
    }
    
    
    private static function scaleMantissa($mantissa, int $scale)
    {
        if (0 === $scale) {
            return null;
        }
        
        if (null === $mantissa) {
            return str_repeat('0', $scale);
        }
        
        $decimals = strlen($mantissa);
        
        // Mantissa is the correct scale.
        if ($scale === $decimals) {
            return $mantissa;
        }
        
        // Mantissa is too long, use bcmul to truncate to the correct scale.
        if ($scale < $decimals) {
            return substr($mantissa, 0, $scale);
        }
        
        // Mantissa is too short, pad with zeroes to the correct scale.
        return sprintf("%0-{$scale}s", $mantissa);
    }
}

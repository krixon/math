<?php

namespace Krixon\Math;

class Decimal
{
    /**
     * @var string
     */
    private $characteristic;
    
    /**
     * @var string|null
     */
    private $mantissa;
    
    
    /**
     * @param string      $characteristic
     * @param string|null $mantissa
     */
    public function __construct(string $characteristic, string $mantissa = null)
    {
        $this->characteristic = bcmul($characteristic, 1);
        $this->mantissa       = $mantissa;
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
        if (strlen($string) === 0 || !preg_match('#^(?<c>\d+)?(?:\.(?<m>\d+))?$#', $string, $matches)) {
            throw new \InvalidArgumentException("Cannot create Decimal from invalid string '$string'.");
        }
    
        $characteristic = $matches['c'] ?? '0';
        $mantissa       = $matches['m'] ?? null;
        
        return new static($characteristic, $mantissa);
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
     * Determines if the decimal is an integer.
     *
     * Note that values such as 10.0 are not considered integers because the .0 is a significant digit. A value is
     * only an integer if it does not have a mantissa at all.
     *
     * @return bool
     */
    public function isInteger()
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
     * Converts the decimal to a string.
     *
     * @return string
     */
    public function toString() : string
    {
        $string = $this->characteristic();
        
        if (!$this->isInteger()) {
            $string .= '.' . $this->mantissa();
        }
        
        return $string;
    }
}

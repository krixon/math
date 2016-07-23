<?php

namespace Krixon\Math;

class Fraction
{
    /**
     * @var int
     */
    private $numerator;
    
    /**
     * @var int
     */
    private $denominator;
    
    /**
     * @var bool|null
     */
    private $isSimplified;
    
    
    public function __construct(int $numerator, int $denominator)
    {
        if (0 === $denominator) {
            throw new \InvalidArgumentException('Denominator cannot be zero.');
        }
        
        $this->numerator   = $numerator;
        $this->denominator = $denominator;
    }
    
    
    /**
     * Creates a new instance from a string of the form "<numerator>/<denominator>", for example "2/3".
     *
     * @param string $string
     *
     * @return Fraction
     */
    public static function fromString(string $string) : Fraction
    {
        if (!preg_match('#^(\d+)/(\d+)$#', $string, $matches)) {
            throw new \InvalidArgumentException("Cannot create Fraction from invalid string '$string'.");
        }
        
        return new static((int)$matches[1], (int)$matches[2]);
    }
    
    
    /**
     * The Fraction's numerator.
     *
     * @return int
     */
    public function numerator() : int
    {
        return $this->numerator;
    }
    
    
    /**
     * The Fraction's denominator.
     *
     * @return int
     */
    public function denominator() : int
    {
        return $this->denominator;
    }
    
    
    /**
     * Determines if the Fraction is simplified.
     *
     * @return bool
     */
    public function isSimplified() : bool
    {
        if (null === $this->isSimplified) {
            $this->isSimplified = $this->greatestCommonDivisor() === 1;
        }
        
        return $this->isSimplified;
    }
    
    
    /**
     * Converts the Fraction to a Ratio.
     *
     * @return Ratio
     */
    public function toRatio() : Ratio
    {
        return new Ratio((string)$this->numerator, (string)$this->denominator);
    }
    
    
    /**
     * Simplifies the Fraction.
     *
     * For example, given a Fraction of 4/8, this will return a Fraction of 1/2.
     *
     * @return Fraction
     */
    public function simplify() : Fraction
    {
        $gcd = $this->greatestCommonDivisor();
        
        // Optimisation: If GCD is 1 then this Fraction is already simplified. Don't call isSimplified() since that
        // might have to calculate GCD again.
        if ($gcd === 1) {
            return $this;
        }
        
        $instance = clone $this;
        
        $instance->numerator   /= $gcd;
        $instance->denominator /= $gcd;
        
        return $instance;
    }
    
    
    /**
     * Calculates the greatest common divisor of the Fraction's numerator and denominator.
     *
     * @return int
     */
    public function greatestCommonDivisor() : int
    {
        $a = $this->numerator;
        $b = $this->denominator;
        
        while (0 !== $b) {
            list ($a, $b) = [$b, $a % $b];
        }
        
        return (int)abs($a);
    }
}

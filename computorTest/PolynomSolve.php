<?php

class PolynomSolve
{
    public $array;
    public $firstSolution = NULL;
    public $secondSolution = NULL;
    public $onlySolution = NULL;

    function __construct($array)
    {
        $this->array = $array;
    }

    function discriminant()
    {
        $this->discriminant = ($this->array["left"]["pow1"] ** 2) - (4 * $this->array["left"]["pow2"] * $this->array["left"]["pow0"]);
        return ($this->discriminant);
    }

    function    getBothSolutions()
    {   
        $this->firstSolution = (- $this->array["left"]["pow1"] - $this->discriminant ** 0.5) / (2 * $this->array["left"]["pow2"]);
        $this->secondSolution = (- $this->array["left"]["pow1"] + $this->discriminant ** 0.5) / (2 * $this->array["left"]["pow2"]);
        echo "The first solution is " . $this->firstSolution . "\nThe second solution is " . $this->secondSolution . "\n";
    }
    
    function getComplexSolution()
    {
        $this->firstSolution = "( -{$this->array["left"]["pow1"]} - i * √(". -$this->discriminant .") ) / ".(2 * $this->array["left"]["pow2"]);
        $this->secondSolution = "( -{$this->array["left"]["pow1"]} + i * √(". -$this->discriminant .") ) / ".(2 * $this->array["left"]["pow2"]);
        echo "The first solution is " . $this->firstSolution . "\nThe second solution is " . $this->secondSolution . "\n";
    }
    
    function    getOnesolution()
    {
        $this->onlySolution = -$this->array["left"]["pow1"] / (2 * $this->array["left"]["pow2"]);
        echo "The only solution is : " . $this->onlySolution. "\n";
    }
    
    function getSolutionWithoutSecond()
    {
        if ($this->array["left"]["pow1"] == 0)
            throw new Exception("Division by zero is not possible");
        $this->onlySolution = -($this->array["left"]["pow0"]) / $this->array["left"]["pow1"];
        echo "The only solution is " . $this->onlySolution . "\n";
    }
}
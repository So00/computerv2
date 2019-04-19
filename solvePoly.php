<?php

class SolvePoly
{
    public $array;
    public $discriminant;
    public $firstSolution = NULL;
    public $secondSolution = NULL;
    public $onlySolution = NULL;

    function __construct()
    {

    }

    function discriminant()
    {
        $this->discriminant = ($this->array["left"]["pow1"] ** 2) - (4 * $this->array["left"]["pow2"] * $this->array["left"]["pow0"]);
    }

    function    solve()
    {
        $this->discriminant();
        if ($this->array["left"]["pow2"] == 0)
            $this->getSolutionWithoutSecond();
        else if ($this->discriminant > 0)
            $this->getBothSolutions();
        else if ($this->discriminant == 0)
            $this->getOneSolution();
        else
            $this->getComplexSolution();
    }

    function    getBothSolutions()
    {
        $value = (- $this->array["left"]["pow1"] - $this->discriminant ** 0.5) / (2 * $this->array["left"]["pow2"]);
        $valueInt = intval($value);
        if ($value == $valueInt)
        $this->firstSolution = (- $this->array["left"]["pow1"] - $this->discriminant ** 0.5) / (2 * $this->array["left"]["pow2"]);
        else
        {
            if (is_int($this->discriminant ** 0.5))
            $this->firstSolution = (- $this->array["left"]["pow1"] - ($this->discriminant ** 0.5)) . " / " . (2 * $this->array["left"]["pow2"]);
            else
            $this->firstSolution = "(".(- $this->array["left"]["pow1"])." - √{$this->discriminant}) / " . (2 * $this->array["left"]["pow2"]);
        }
        $value = (- $this->array["left"]["pow1"] + $this->discriminant ** 0.5) / (2 * $this->array["left"]["pow2"]);
        $valueInt = intval($value);
        if ($value == $valueInt)
            $this->secondSolution = (- $this->array["left"]["pow1"] + $this->discriminant ** 0.5) / (2 * $this->array["left"]["pow2"]);
        else
        {
            if (is_int($this->discriminant ** 0.5))
                $this->secondSolution = (- $this->array["left"]["pow1"] + ($this->discriminant ** 0.5)) . " / " . (2 * $this->array["left"]["pow2"]);
            else
                $this->secondSolution = "(".(- $this->array["left"]["pow1"])." + √{$this->discriminant}) / " . (2 * $this->array["left"]["pow2"]);
        }
        echo $this->firstSolution . "\n" . $this->secondSolution . "\n";
    }
    
    function getComplexSolution()
    {
        if ($this->array["left"]["pow2"] == 0)
            throw new Exception("Division by zero is not possible");
        $this->firstSolution = "( -{$this->array["left"]["pow1"]} - i * √(". -$this->discriminant .") ) / ".(2 * $this->array["left"]["pow2"]);
        $this->secondSolution = "( -{$this->array["left"]["pow1"]} + i * √(". -$this->discriminant .") ) / ".(2 * $this->array["left"]["pow2"]);
        echo $this->firstSolution . "\n" . $this->secondSolution . "\n";
    }
    
    function    getOnesolution()
    {

        if (is_int(-$this->array["left"]["pow1"] / (2 * $this->array["left"]["pow2"])))
            $this->onlySolution = -$this->array["left"]["pow1"] / (2 * $this->array["left"]["pow2"]);
        else
            $this->onlySolution = "-{$this->array["left"]["pow1"]} / (2 * {$this->array["left"]["pow2"]}";
        echo $this->onlySolution . "\n";
    }
    
    function getSolutionWithoutSecond()
    {

        if (is_int(-($this->array["left"]["pow0"]) / $this->array["left"]["pow1"]))
            $this->onlySolution = -($this->array["left"]["pow0"]) / $this->array["left"]["pow1"];
        else
            $this->onlySolution = "-{$this->array["left"]["pow0"]} / {$this->array["left"]["pow1"]}";
        echo $this->onlySolution . "\n";
    }
}
<?php

class Solve
{
    public $array;
    public $options;
    public $firstSolution = NULL;
    public $secondSolution = NULL;
    public $onlySolution = NULL;

    function __construct($array, $options)
    {
        $this->array = $array;
        $this->options = $options;
    }

    function discriminant()
    {
        if (!empty($this->options["step"]))
        {
            echo "\nDiscriminant solve : \n";
            echo "  ({$this->array["left"]["pow1"]} ^ 2) - (4 * {$this->array["left"]["pow2"]} * {$this->array["left"]["pow0"]})\n";
            echo "  " . ($this->array["left"]["pow1"] ** 2) ." - (4 * {$this->array["left"]["pow2"]} * {$this->array["left"]["pow0"]})\n";
            echo "  " . ($this->array["left"]["pow1"] ** 2) ." - ". (4 * $this->array["left"]["pow2"] * $this->array["left"]["pow0"])."\n";
            echo "  " . (($this->array["left"]["pow1"] ** 2) - (4 * $this->array["left"]["pow2"] * $this->array["left"]["pow0"])) . "\n";
            echo "\n";
        }
        $this->discriminant = ($this->array["left"]["pow1"] ** 2) - (4 * $this->array["left"]["pow2"] * $this->array["left"]["pow0"]);
        return ($this->discriminant);
    }

    function    getBothSolutions()
    {
        if (!empty($this->options["step"]))
        {
            echo "Solve first solution : \n";
            echo "  (- {$this->array["left"]["pow1"]} - √{$this->discriminant}) / (2 * {$this->array["left"]["pow2"]})\n";
            echo "  (- {$this->array["left"]["pow1"]} - ". ($this->discriminant ** 0.5) .") / (2 * {$this->array["left"]["pow2"]})\n";
            echo "  ". (- $this->array["left"]["pow1"] - ($this->discriminant ** 0.5)) . " / " . (2 * $this->array["left"]["pow2"]) . "\n";
            echo "  ". ((- $this->array["left"]["pow1"] - ($this->discriminant ** 0.5)) / (2 * $this->array["left"]["pow2"])) . "\n";
            echo "\nSolve second solution : \n";
            echo "  (- {$this->array["left"]["pow1"]} + √{$this->discriminant}) / (2 * {$this->array["left"]["pow2"]})\n";
            echo "  (- {$this->array["left"]["pow1"]} + ". ($this->discriminant ** 0.5) .") / (2 * {$this->array["left"]["pow2"]})\n";
            echo "  ". (- $this->array["left"]["pow1"] + ($this->discriminant ** 0.5)) . " / " . (2 * $this->array["left"]["pow2"]) . "\n";
            echo "  ". ((- $this->array["left"]["pow1"] + ($this->discriminant ** 0.5)) / (2 * $this->array["left"]["pow2"])) . "\n";
            echo "\n";
        }
        
        
        if (empty($this->options["fraction"]))
        {
            $this->firstSolution = (- $this->array["left"]["pow1"] - $this->discriminant ** 0.5) / (2 * $this->array["left"]["pow2"]);
            $this->secondSolution = (- $this->array["left"]["pow1"] + $this->discriminant ** 0.5) / (2 * $this->array["left"]["pow2"]);
        }
        else
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
        }

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
        if (!empty($this->options["step"]))
        {
            echo "\nSolving one solution : \n";
            echo "  -{$this->array["left"]["pow1"]} / (2 * {$this->array["left"]["pow2"]})\n";
            echo "  -{$this->array["left"]["pow1"]} / " . (2 * $this->array["left"]["pow2"]) . "\n";
            echo "  ". (-$this->array["left"]["pow1"] / (2 * $this->array["left"]["pow2"])) . "\n";
            echo "\n";
        }
        if (empty($this->options["fraction"]))
            $this->onlySolution = -$this->array["left"]["pow1"] / (2 * $this->array["left"]["pow2"]);
        else
        {
            if (is_int(-$this->array["left"]["pow1"] / (2 * $this->array["left"]["pow2"])))
                $this->onlySolution = -$this->array["left"]["pow1"] / (2 * $this->array["left"]["pow2"]);
            else
                $this->onlySolution = "-{$this->array["left"]["pow1"]} / (2 * {$this->array["left"]["pow2"]}";
        }
        echo "The only solution is : " . $this->onlySolution. "\n";
    }
    
    function getSolutionWithoutSecond()
    {
        if ($this->array["left"]["pow1"] == 0)
            throw new Exception("Division by zero is not possible");
        if (!empty($this->options["step"]))
        {
            echo "\nSolving one solution\n";
            echo "  -{$this->array["left"]["pow0"]} / {$this->array["left"]["pow1"]}\n";
            echo "  " . -$this->array["left"]["pow0"] / $this->array["left"]["pow1"] . "\n";
        }
        if (empty($this->options["fraction"]))
            $this->onlySolution = -($this->array["left"]["pow0"]) / $this->array["left"]["pow1"];
        else
        {
            if (is_int(-($this->array["left"]["pow0"]) / $this->array["left"]["pow1"]))
                $this->onlySolution = -($this->array["left"]["pow0"]) / $this->array["left"]["pow1"];
            else
                $this->onlySolution = "-{$this->array["left"]["pow0"]} / {$this->array["left"]["pow1"]}";
        }
        echo "The only solution is " . $this->onlySolution . "\n";
    }
}
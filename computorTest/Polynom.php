<?php

include_once "PolynomParse.php";
include_once "PolynomSolve.php";

class Polynom
{
    function __construct()
    {
        
    }

    function solve($left, $right)
    {
        $parser = new PolynomParse($left, $right);
        $data = $parser->getOp();
        $parser->reducedForm();
        $solver = new PolynomSolve($data);
        if ($solver->array["left"]["pow2"])
        {
            $discri = $solver->discriminant();
            echo "The delta is " . $discri . "\n";
            if ($discri > 0)
                $solver->getBothSolutions();
            else if ($discri == 0)
                $solver->getOneSolution();
            else
                $solver->getComplexSolution();
        }
        else
        {
            echo "The delta doesn't matter in first degre polynom\n";
            $solver->getSolutionWithoutSecond();
        }
    }
}
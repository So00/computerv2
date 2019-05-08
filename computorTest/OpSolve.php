<?php

include_once "OpValidator.php";
include_once "Data.php";

class OpSolve{

    static public $operator = ["+", "-", "%", "/", "*", "(", ")"];
    static public $operation = ["+" => "add", "%" => "modulo", "-" => "minus", "/" => "div", "*" => "mult", "^" => "power"];

    static function add($left, $right)
    {
        if ($right == false)
            throw new Exception("Nothing after your minus for $left");
        return (floatval($left) + floatval($right));
    }
    
    static function power($left, $right)
    {
        if ($right === false)
            throw new Exception("Nothing after your power for $left");
        if ($left !== "i")
            return (floatval($left) ** floatval($right));
        switch ($right % 4)
        {
            case (0) :
                return ("1");
            case (1) :
                return ("i");
            case (2) :
                return ("-1");
            case (3) :
                return ("-1*i");
        }
    }

    static function minus($left, $right)
    {
        if ($right == false)
            throw new Exception("Nothing after your minus for $left");
        return (floatval($left) - floatval($right));
    }
    
    static function replaceSimpleI(&$left, &$right)
    {
        $positiv = ["i", "+i"];
        if (array_search($left, $positiv) !== false)
            $left = "1";
        if (array_search($right, $positiv) !== false)
            $right = "1";
        if ($left === "-i")
            $left = "-1";
        if ($right === "-i")
            $right = "-1";
    }

    static function getImaginaryPower($left, $right, $op)
    {
        $pow = 0;
        if (strstr($left, "i") !== false)
            $pow++;
        if (strstr($right, "i") !== false)
        {
            if ($op === "mult")
                $pow++;
            if ($op === "div")
                $pow--;
        }
        return ($pow);
    }

    static function div($left, $right)
    {
        if ($right == false)
            throw new Exception("Nothing after your division for $left");
        $iPow = OpSolve::getImaginaryPower($left, $right, "div");
        OpSolve::replaceSimpleI($left, $right);
        if (floatval($right) === 0.0)
            throw new Exception("Can't divise by 0");
        $result = floatval($left) / floatval($right);
        if ($iPow === 1)
            return ($result."i");
        else if ($iPow === -1)
            return ($result . "/i");
        return ($result);
    }

    static function mult($left, $right)
    {
        if ($right == false)
            throw new Exception("Nothing after your multiplication for $left");
        $iPow = OpSolve::getImaginaryPower($left, $right, "mult");
        OpSolve::replaceSimpleI($left, $right);
        $result = floatval($left) * floatval($right);
        if ($iPow === 1)
            return ($result."i");
        else if ($iPow === 2)
            return ($result * -1);
        return ($result);
    }
    
    static function modulo($left, $right)
    {
        if ($right == false)
            throw new Exception("Nothing after your modulo for $left");
        return (floatval($left) % floatval($right));
    }

    static function priorOp($op, $lastPossible)
    {
        $begin = ($lastPossible === NULL ? 0 : $lastPossible);
        $firstOp = strpos($op, "(", $begin);
        if ($firstOp === false && preg_match("/[\*\/\%\^]/i", $op) !== 0)
        {
            $firstOp = strpos($op, "/", $begin);
            if ((($tmp = strpos($op, "%", $begin)) && $tmp < $firstOp) || $firstOp === false)
                $firstOp = $tmp;
            if ((($tmp = strpos($op, "*", $begin)) && $tmp < $firstOp) || $firstOp === false)
                $firstOp = $tmp;
            if ((($tmp = strpos($op, "^", $begin))) || $firstOp === false)
                $firstOp = $tmp;
        }
        if ($firstOp === false)
            return (false);
        return ($firstOp);
    }

    /**
     * Search the first operation possible
     */
    static function getFirstOperandPos($op, $lastPossible)
    {
        $len = strlen($op);
        for ($i = ($lastPossible == NULL ? 0 : $lastPossible); $i < $len; $i++)
        {
            if (array_search($op[$i], OpSolve::$operator) !== false && $i !== 0)
                return ($i);
            if ($op[$i] === "[")
                return (OpValidator::endOfMatrice($op, $i + 1));
        }
        return ($i !== $len ? $i : false);
    }

    /**
     * Do the operation
     * $op[$pos] is the operator
     */
    static function replaceSimpleOp(&$op, $pos, $lastPos, $nextPos, &$lastPossible)
    {
        $left = substr($op, $lastPos, $pos - $lastPos);
        $right = substr($op, $pos + 1, $nextPos - $pos);
        $replacement = OpSolve::{OpSolve::$operation[$op[$pos]]}($left, $right);
        $to_search = substr($op, $lastPos, $nextPos + 1 - $lastPos);
        $op = str_replace($to_search, $replacement, $op);
        if ($op[$pos] === "/" && OpSolve::getImaginaryPower($left, $right, "div") === -1)
            $lastPossible = $lastPos + strlen($replacement);
    }

    static function getLastNb($op, $pos)
    {
        $pos--;
        for (; $pos !== 0 && (is_numeric($op[$pos]) || $op[$pos] === "." || $op[$pos] === "i"); $pos--);
        return ($pos ? $pos + 1 : 0);
    }

    static function getNextnb($op, $pos)
    {
        $pos++;
        $len = strlen($op);
        if ($op[$pos] === "-")
            $pos++;
        for (; $pos < $len && (is_numeric($op[$pos]) || $op[$pos] === "." || $op[$pos] === "i"); $pos++);
        return ($pos ? $pos - 1 : 0);
    }

    static function getBracketsEnd($op, $begin)
    {
        $brackets = 1;
        $len = strlen($op);
        if ($op[$begin] === "(")
            $begin++;
        for ($end = $begin; $brackets && $end < $len; $end++)
            if ($op[$end] === "(")
                $brackets++;
            else if ($op[$end] === ")")
                $brackets--;
        if ($brackets)
            return (false);
        return ($end);
    }

    static function replaceBrackets(&$op, $pos, $data)
    {
        $end = OpSolve::getBracketsEnd($op, $pos);
        $solve = OpSolve::solve(substr($op, $pos + 1, ($end - 1) - ($pos + 1)), $data);
        $op = str_replace(substr($op, $pos, $end - $pos), $solve, $op);
    }

    static function matriceSolve($op, $data)
    {
        $matrice = OpValidator::isMatrice($op, $data);
        return ($matrice);
    }

    static function solve ($op, $data)
    {
        $op = OpValidator::replaceSpace($op, $data);
        $op = OpValidator::replaceAllFun($op, $data);
        $op = preg_replace("/\s/", "", $op);
        if (!OpValidator::checkRightOperand($op, $data))
            throw new Exception("$op is not a valid operation");
        if (strpos($op, "[") !== false)
            return (OpSolve::matriceSolve($op, $data));
        $lastPossible = NULL;
        while (($prior = OpSolve::priorOp($op, $lastPossible)) !== false)
        {
            if ($op[$prior] === "(")
                OpSolve::replaceBrackets($op, $prior, $data);
            else
            {
                $lastNum = OpSolve::getLastNb($op, $prior);
                $nextNum = OpSolve::getNextnb($op, $prior);
                OpSolve::replaceSimpleOp($op, $prior, $lastNum, $nextNum, $lastPossible);
            }
        }
        $lastPossible = NULL;
        // while (($nextOp = OpSolve::getFirstOperandPos($op, $lastPossible)) !== false)
        // {
        //     $lastNum = OpSolve::getLastNb($op, $nextOp);
        //     $nextNum = OpSolve::getNextnb($op, $nextOp);
        //     OpSolve::replaceSimpleOp($op, $nextOp, $lastNum, $nextNum, $lastPossible);
        // }
        return ($op);
    }

}

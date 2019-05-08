<?php

include_once "OpValidator.php";
include_once "Data.php";

class OpSolve{

    static public $operator = ["+", "-", "%", "/", "*", "(", ")"];
    static public $operation = ["+" => "add", "%" => "modulo", "-" => "minus", "/" => "div", "*" => "mult", "^" => "power"];

    static function add($left, $right)
    {
        if (isset($right))
            return (floatval($left) + floatval($right));
        return (floatval($left));
    }
    
    static function power($left, $right)
    {
        return (floatval($left) ** floatval($right));
    }

    static function minus($left, $right)
    {
        if (isset($right))
            return (floatval($left) - floatval($right));
        return (floatval($left));
    }
    
    static function div($left, $right)
    {
        if (isset($right))
            return (floatval($left) / floatval($right));
        return ($left);
    }
    
    static function mult($left, $right)
    {
        if (isset($right))
            return (floatval($left) * floatval($right));
        return (floatval($left));
    }
    
    static function modulo($left, $right)
    {
        if (isset($right))
            return (floatval($left) % floatval($right));
        return (floatval($left));
    }

    static function priorOp($op)
    {
        $firstOp = 0;
        $firstOp = strpos($op, "(");
        if ($firstOp === false && preg_match("/[\*\/\%\^]/i", $op) !== 0)
        {
            $firstOp = strpos($op, "/");
            if ((($tmp = strpos($op, "%")) && $tmp < $firstOp) || $firstOp === false)
                $firstOp = $tmp;
            if ((($tmp = strpos($op, "*")) && $tmp < $firstOp) || $firstOp === false)
                $firstOp = $tmp;
            if ((($tmp = strpos($op, "^"))) || $firstOp === false)
                $firstOp = $tmp;
        }
        if ($firstOp === false)
            return (false);
        return ($firstOp);
    }

    /**
     * Search the first operation possible
     */
    static function getFirstOperandPos($op)
    {
        $len = strlen($op);
        for ($i = 0; $i < $len; $i++)
        {
            if (array_search($op[$i], OpSolve::$operator) !== false && $i !== 0)
                return ($i);
            if ($op[$i] === "[")
                return (OpValidator::endOfMatrice($op, $i + 1));
        }
        return ($i !== $len ? $i : 0);
    }

    /**
     * Do the operation
     * $op[$pos] is the operator
     */
    static function replaceSimpleOp(&$op, $pos, $lastPos, $nextPos)
    {
        $left = substr($op, $lastPos, $pos - $lastPos);
        $right = substr($op, $pos + 1, $nextPos - $pos);
        $replacement = OpSolve::{OpSolve::$operation[$op[$pos]]}($left, $right);
        $to_search = substr($op, $lastPos, $nextPos + 1 - $lastPos);
        $op = str_replace($to_search, $replacement, $op);
    }

    static function getLastNb($op, $pos)
    {
        $pos--;
        for (; $pos !== 0 && (is_numeric($op[$pos]) || $op[$pos] === "."); $pos--);
        return ($pos ? $pos + 1 : 0);
    }

    static function getNextnb($op, $pos)
    {
        $pos++;
        $len = strlen($op);
        if ($op[$pos] === "-")
            $pos++;
        for (; $pos < $len && (is_numeric($op[$pos]) || $op[$pos] === "."); $pos++);
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
        while (($prior = OpSolve::priorOp($op)) !== false)
        {
            if ($op[$prior] === "(")
                OpSolve::replaceBrackets($op, $prior, $data);
            else
            {
                $lastNum = OpSolve::getLastNb($op, $prior);
                $nextNum = OpSolve::getNextnb($op, $prior);
                OpSolve::replaceSimpleOp($op, $prior, $lastNum, $nextNum);
            }
        }
        while (($nextOp = OpSolve::getFirstOperandPos($op)))
        {
            $lastNum = OpSolve::getLastNb($op, $nextOp);
            $nextNum = OpSolve::getNextnb($op, $nextOp);
            OpSolve::replaceSimpleOp($op, $nextOp, $lastNum, $nextNum);
        }
        return ($op);
    }

}

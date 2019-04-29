<?php

include_once "OpValidator.php";
include_once "Data.php";

class OpSolve{

    static public $operator = ["+", "-", "%", "/", "*", "(", ")"];

    static function getBrackets($op)
    {

    }

    static function add($left, $right)
    {
        if (isset($right))
            return (floatval($left) + floatval($right));
        return (floatval($left));
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

    static function getFirstOperandPos($op, $position)
    {
        for ($i = $position; $i < strlen($op); $i++)
        {
            if (array_search($op[$i], OpSolve::$operator) !== false && $i !== 0)
                return ($i);
            if ($op[$i] === "[")
                return (OpValidator::endOfMatrice($op, $i + 1));
        }
        return ($i);
    }

    static function replaceSimpleOp(&$op, $pos, &$lastPos)
    {
        $ret = array();
        $left = substr($op, $lastPos, $pos - $lastPos);
        $nextOp = OpSolve::getFirstOperandPos($op, $pos + 1);
        if ($nextOp <= strlen($op))
            $right = substr($op, $pos + 1, $nextOp - ($pos + 1));
        else
            return (0);
        switch ($op[$pos])
        {
            case "+" :
                $replacement = OpSolve::add($left, $right);
            break;
            case "/" :
                $replacement = OpSolve::add($left, $right);
            break;
            case "%" :
                $replacement = OpSolve::add($left, $right);
            break;
            case "*" :
                $replacement = OpSolve::add($left, $right);
            break;
            case "-" :
                $replacement = OpSolve::add($left, $right);
            break;
        }
        echo str_replace(substr($op, $lastPos, $nextOp - $lastPos - 1), $replacement, $op) . " << result \n";
        $op = str_replace(substr($op, $lastPos, $nextOp - $lastPos - 1), $replacement, $op);
        $lastPos += strlen($replacement);
    }
    
    static function solve ($op, $data)
    {
        if (!OpValidator::checkRightOperand($op, $data))
            throw new Exception("$op is not a valid operation");
        $i = 0;
        $nextOp = OpSolve::getFirstOperandPos($op, $i);
        while(OpSolve::replaceSimpleOp($op, $nextOp, $i))
        {
            echo $op."\n\n";
            if ($op[$i] === "(")
                $i++;
            $nextOp = OpSolve::getFirstOperandPos($op, $i);
            $value = OpSolve::replaceSimpleOp($op, $nextOp, $i);
        }
    }

}
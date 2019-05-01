<?php

include_once "OpValidator.php";
include_once "Data.php";

class OpSolve{

    static public $operator = ["+", "-", "%", "/", "*", "(", ")"];
    static public $operation = ["+" => "add", "%" => "modulo", "-" => "minus", "/" => "div", "*" => "mult"];

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

    /**
     * Do the operation
     * $op[$pos] is the operator
     */
    static function replaceSimpleOp(&$op, $pos, $lastPos)
    {
        $ret = array();
        $left = substr($op, $lastPos, $pos - $lastPos);
        $nextOp = OpSolve::getFirstOperandPos($op, $pos + 1);
        if ($nextOp <= strlen($op))
            $right = substr($op, $pos + 1, $nextOp - ($pos + 1));
        else
            return (strlen($op));
        $replacement = OpSolve::{OpSolve::$operation[$op[$pos]]}($left, $right);
        $to_search = substr($op, $lastPos, $nextOp - $lastPos);
        $op = str_replace($to_search, $replacement, $op);
        return ($lastPos);
    }

    static function solve ($op, $data)
    {
        if (!OpValidator::checkRightOperand($op, $data))
            throw new Exception("$op is not a valid operation");
        $i = 0;
        $nextOp = OpSolve::getFirstOperandPos($op, $i);
        while($i < strlen($op))
        {
            if ($op[$i] === "(")
                $i++;
            $i = OpSolve::replaceSimpleOp($op, $nextOp, $i);
            $nextOp = OpSolve::getFirstOperandPos($op, $i);
        }
        echo "$op\n";
    }

}
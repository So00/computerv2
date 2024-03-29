<?php

include_once "OpValidator.php";
include_once "Data.php";
include_once "MatriceSolve.php";

class OpSolve{

    static public $operator = ["+", "-", "%", "/", "*", "(", ")"];
    static public $operation = ["%" => "modulo", "/" => "div", "*" => "mult", "^" => "power"];
    
    /** Change the power by their result for imaginay and real */
    static function power($left, $right)
    {
        if ($right === false)
            throw new Exception("Nothing after your power for $left");
        if (intval($right) != floatval($right))
            throw new Exception("Can't handle float power");
        if (strpos($right, "i") !== false)
            throw new Exception("i as power? real?");
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

    /** Replace i if it is alone  */
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

    /** Check the power of a imaginary number and return it */
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
        if ($right === false)
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
        if ($right === false)
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
        if ($right === false)
            throw new Exception("Nothing after your modulo for $left");
        $iPow = OpSolve::getImaginaryPower($left, $right, "div");
        OpSolve::replaceSimpleI($left, $right);
        if (floatval($right) === 0.0)
            throw new Exception("Can't divise by 0");
        $result = floatval($left) / floatval($right);
        if ($iPow === 1)
            return ($result."i");
        else if ($iPow === -1)
            throw new Exception("Really? %i ?");
        return ($result);
    }

    /** Get the prior operation first
     *  $lastpossible is the last possible prior op
     *  ex : can't do 1/i >> we go to next
     * */
    static function priorOp($op, $lastPossible)
    {
        $begin = ($lastPossible === NULL ? 0 : $lastPossible);
        $firstOp = strpos($op, "^", $begin);
        if ($firstOp === false)
            $firstOp = strpos($op, "(", $begin);
        if ($firstOp === false && preg_match("/[\*\/\%\^]/i", $op) !== 0)
        {
            $firstOp = strpos($op, "/", $begin);
            if ((($tmp = strpos($op, "%", $begin)) && $tmp < $firstOp) || $firstOp === false)
                $firstOp = $tmp;
            if ((($tmp = strpos($op, "*", $begin)) && $tmp < $firstOp) || $firstOp === false)
                $firstOp = $tmp;
        }
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
        if ($op[$pos] === "/" && $right === "i")
            $lastPossible = $lastPos + strlen($replacement);
    }

    /** Get the number that preceed the position */
    static function getLastNb($op, $pos)
    {
        $pos--;
        for (; $pos !== 0 && (is_numeric($op[$pos]) || $op[$pos] === "." || $op[$pos] === "i"); $pos--);
        return ($pos ? $pos + 1 : 0);
    }

    /** Get the next number until it's valid from a position */
    static function getNextnb($op, $pos)
    {
        $pos++;
        $len = strlen($op);
        if ($op[$pos] === "-")
            $pos++;
        for (; $pos < $len && (is_numeric($op[$pos]) || $op[$pos] === "." || $op[$pos] === "i"); $pos++);
        return ($pos ? $pos - 1 : 0);
    }

    /** Get the end of a brackets */
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

    /** Place the mult from outside in the brackets */
    static function multBrackets($multOp)
    {
        $number = substr($multOp, 0, strpos($multOp, "("));
        $replacement = "(";
        $depth = 0;
        $brPos = strpos($multOp, "(", 0);
        for ($i = $brPos + 1; $i < strlen($multOp); $i++)
        {
            if ($i === $brPos + 1)
                $replacement .= $number;
            if ($multOp[$i] === "(")
                $depth++;
            if ($multOp[$i] === ")")
                $depth--;
            $replacement .= $multOp[$i];
            if (($multOp[$i] === "+" || $multOp[$i] === "-")
                && (is_numeric($multOp[$i - 1]) || $multOp[$i - 1] === "i" || $multOp[$i - 1]) && $depth === 0 && $i !== $brPos + 1)
                $replacement .= $number;
        }
        return ($replacement);
    }

    /** Place the division from outside in the brackets */
    static function divBrackets($op, $division)
    {
        $replacement = "(";
        $depth = 0;
        $len = strlen($op);
        for ($i = 0; $i < $len; $i++)
        {
            if ((($op[$i] === "+" || $op[$i] === "-") && (is_numeric($op[$i - 1]) || $op[$i - 1] === "i") && !$depth)
            || $i === $len
            || ($op[$i - 1] === ")" && !$depth) && $i > 0)
                $replacement .= "$division";
            if ($op[$i] === "(")
                $depth++;
            if ($op[$i] === ")")
                $depth--;
            $replacement .= $op[$i];
        }
        return ($replacement);
    }

    /** Calculate and replace the brackets by the result */
    static function replaceBrackets(&$op, $pos, $data)
    {
        $end = OpSolve::getBracketsEnd($op, $pos);
        while ($op[$pos - 1] === "*")
        {
            $lastNum = OpSolve::getLastNb($op, $pos - 1);
            if ($op[$lastNum - 1] === "-")
                $lastNum--;
            $multOp = substr($op, $lastNum, $end - $lastNum);
            $replacement = OpSolve::multBrackets($multOp);
            $op = str_replace($multOp, $replacement, $op);
            OpSolve::replaceSign($op);
            $pos = strpos($op, "(");
            $end = OpSolve::getBracketsEnd($op, $pos);
        }
        while ($op[$end] === "/")
        {
            $nextNum = OpSolve::getNextnb($op, $end);
            $num = substr($op, $end, $nextNum - $end + 1);
            $replacement = OpSolve::divBrackets(substr($op, $pos + 1, $end - ($pos + 1)), $num);
            $op = str_replace(substr($op, $pos,  $nextNum - $pos + 1), $replacement, $op);
            OpSolve::replaceSign($op);
            $pos = strpos($op, "(");
            $end = OpSolve::getBracketsEnd($op, $pos);
        }
        $solve = OpSolve::solve(substr($op, $pos + 1, ($end - 1) - ($pos + 1)), $data);
        $op = str_replace(substr($op, $pos, $end - $pos), $solve, $op);
    }

    /** Split and calculate the basic op by order (i^0, i^1, /i) */
    static function splitBasic($op)
    {
        $basicOp = ["+", "-"];
        OpSolve::replaceSign($op);
        $result =   ["iPow" => 0,
                     "noPow" => 0,
                     "iDiv" => 0
        ];
        for ($i = 0; $i < strlen($op); $i++)
        {
            $num = OpSolve::getNextnb($op, ($i ? $i : $i - 1));
            if ($op[$num + 1] === "/")
                $num = OpSolve::getNextnb($op, $num + 1);
            $number = substr($op, $i, $num + 1 - $i);
            if (strstr($number, "/i") && is_numeric(str_replace("/i", "", $number)))
                $result["iDiv"] += floatval($number);
            else if (strstr($number, "i") && ($number === "i" || $number === "+i" || $number === "-i" || is_numeric(str_replace("i", "", $number))))
            {
                if ($number === "i" || $number === "+i")
                    $number = "1";
                if ($number === "-i")
                    $number = "-1";
                $result["iPow"] += floatval($number);
            }
            else if (is_numeric($number))
                $result["noPow"] += floatval($number);
            else
                throw new Exception("Error here : $number");
            $i = $num;
        }
        $return = ($result["noPow"] || (!$result["iPow"] && !$result["iDiv"])? $result["noPow"] : "");
        if ($result["iPow"])
            $return .= ($return !== "" && $result["iPow"] > 0 ? "+" : "") . $result["iPow"]."i";
        if ($result["iDiv"])
            $return .=  ($return !== "" && $result["iDiv"] > 0 ? "+" : "") . $result["iDiv"]."/i";
        return ($return);
    }

    /** Replace double sign like ++ -+ +- -- */
    static function replaceSign(&$op)
    {
        $replace = 1;
        while ($replace)
        {
            $replace=0;
            $op = str_replace(["+-", "-+"], "-", $op, $tmp);
            if ($tmp)
                $replace = $tmp;
            $op = str_replace("--", "+", $op, $tmp);
            if ($tmp)
                $replace = $tmp;
            $op = str_replace("++", "+", $op, $tmp);
            if ($tmp)
                $replace = $tmp;
        }
    }

    /** Expand the brackets when they are multiplied from outside or multiplied each other */
    static function expandBracketsPow($op)
    {
        if (preg_match_all("/(\(.*\))\^([0-9]+\.?([0-9]+))/U", $op, $allPow))
        {
            foreach ($allPow[1] as $key => $actOp)
            {
                $replacement = "";
                if (strstr($allPow[2][$key], "."))
                    throw new Exception("Can't handle float power on brackets, sorry :)");
                for ($i = intval($allPow[2][$key]); $i; $i--)
                    $replacement .= $actOp . ($i !== 1 ? "*" : "");
                $op = str_replace($allPow[0][$key], $replacement, $op);
            }
        }
        OpSolve::replaceSign($op);
        while (preg_match_all("/(\(.*\))\*(\(.*\))/U", $op, $allMult))
        {
            foreach ($allMult[1] as $key => $actOp)
            {
                $leftOp = $allMult[2][$key];
                $replacement = "";
                for ($i = 0; $i < strlen($leftOp); $i++)
                {
                    $replacement .= $leftOp[$i];
                    if (($leftOp[$i + 1] == "+" || $leftOp[$i + 1] == "-" || $leftOp[$i + 1] == ")") && $i !== 0)
                    {
                        if ($leftOp[$i] !== "*")
                            $replacement .= "*$actOp";
                    }
                    if ($leftOp[$i + 1] == "/")
                    {
                        $replacement .= "*$actOp";
                        $replacement .= "/";
                        $i += 2;
                        if ($leftOp[$i] === "-" || $leftOp[$i] === "+")
                        {
                            $replacement .= $leftOp[$i];
                            $i++;
                        }
                        while ($leftOp[$i] !== "+" && $leftOp[$i] !== "-" && $leftOp[$i] !== ")")
                        {
                            $replacement .= $leftOp[$i];
                            $i++;
                        }
                        $i--;
                    }
                }
                $op = str_replace($allMult[0][$key], $replacement, $op);
            }
        }
        OpSolve::replaceSign($op);
        return ($op);
    }

    static function solve ($op, $data)
    {
        $op = OpValidator::replaceSpace($op, $data);
        $op = OpValidator::replaceAllFun($op, $data);
        $op = preg_replace("/\s/", "", $op);
        if (!OpValidator::checkRightOperand($op, $data))
            throw new Exception("$op is not a valid operation");
        if (strpos($op, "[") !== false)
            return (MatriceSolve::matriceSolve($op, $data));
        $lastPossible = NULL;
        $op = OpSolve::expandBracketsPow($op);
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
        $op = OpSolve::splitBasic($op);
        return ($op);
    }

}

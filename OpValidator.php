<?php

class OpValidator
{
    private function construct() {}

    static function isalpha($c)
    {
        if (($c >= 'a' && $c <= 'z') || ($c >= 'A' && $c <= 'Z'))
            return (1);
        return (0);
    }
    
    static function isdigit($c)
    {
        if ($c >= '0' && $c <= '9')
            return (1);
        return (0);
    }

    static function replaceSpace($str, $var)
    {
        $str = preg_replace("/([a-z]+)\s([a-z]+)/", "$1*$2", $str);
        $str = preg_replace("/([0-9]+\.?[0-9]*)\s?([a-z]+)/", "$1*$2", $str);
        $str = preg_replace("/(\(.*\))\s?(\(.*\))/", "$1*$2", $str);
        $str = preg_replace("/(\(.*\))\s?([a-z]+)/", "$1*$2", $str);
        $str = preg_replace("/([0-9]+)\s?(\(.*\))/", "$1*$2", $str);
        $str = preg_replace("/(\(.*\))\s?([0-9]+)/", "$1*$2", $str);
        $str = preg_replace("/([a-z]+)\s?([0-9]+\.?[0-9]*)/", "$1*$2", $str);
        $str = preg_replace("/\s/", "", $str);
        if (count($var->var))
            foreach ($var->var as $key => $actVar)
                $str = preg_replace("/($key)\s?(\(.*\))/", "$1*$2", $str);
        return ($str);
    }

    static function validBrackets($string)
    {
        while (($opBracket = strpos($string, "(", 0)) !== false && ($clBrackets = strpos($string, ")", 0)) !== false)
        {
            if ($clBrackets < $opBracket)
                return (0);
            $string[$opBracket] = " ";
            $string[$clBrackets] = " ";
        }
        $opBracket = strpos($string, "(", 0);
        $clBrackets = strpos($string, ")", 0);
        if (($opBracket && $clBrackets === false) || ($opBracket === false && $clBrackets))
            return (0);
        return (1);
    }

    static function isMatrice($str, $var, $fun)
    {
        if (preg_match("/^\[(\[(.*(,.*)*)\](;\[(.*(,.*)*)\]))\]$/i", $str, $test))
        {
            $allLign = $test[1];
            $allLign = explode("];[", substr($allLign, 1, strlen($allLign) - 2));
            $sizeLign = NULL;
            foreach ($allLign as $key => $lignValue)
            {

                $arrayOfValue = explode(",", $lignValue);
                if ($sizeLign === NULL)
                    $sizeLign = count($arrayOfValue);
                if ($sizeLign !== count($arrayOfValue))
                    throw new Exception("Your matrice lines do not have the same size");
                foreach ($arrayOfValue as $actLignValue)
                {
                    if (empty($actLignValue))
                        throw new Exception("A variable in your matrice is not set");
                    if (!OpValidator::checkSemanticOp($actLignValue, $var, $fun))
                        return (0);
                    $save[$key][] = $actLignValue;
                }
            }
            return ($save);
        }
        return (0);
    }

    static function isVar($str, $var)
    {
        if (isset($var->var[$str]))
            return (1);
        return (0);
    }

    static function isFun($name, $op, $i, &$nextOp, $fun)
    {
        if (!$fun->issetFunName($name))
            return (0);
        $end = strpos($op, ")", $nextOp);
        if ($end === false)
            return (0);
        echo substr($op, $i, $end-$i + 1);
        if ($fun->isFun(substr($op, $i, $end-$i + 1)))
        {
            $nextOp = $end;
            return (1);
        }
        return (0);
    }

    static function isValidNumber($str)
    {
        if (preg_match("/^([0-9]+(\.[0-9]+)?)$/", $str))
            return (1);
        return (0);
    }

    static function endOfMatrice($op, $pos)
    {
        $brackets = 1;
        while ($pos < strlen($op))
        {
            if ($op[$pos] === "[")
                $brackets++;
            if ($op[$pos] === "]")
                $brackets--;
            if ($brackets === 0)
                break;
            $pos++;
        }
        if ($pos !== strlen($op))
            return ($pos + 1);
        return (false);
    }

    static function strgetpos($op, $operator, $position)
    {
        for ($i = $position; $i < strlen($op); $i++)
        {
            if (array_search($op[$i], $operator) !== false && $i !== 0)
                return ($i);
            if ($op[$i] === "[")
                return (OpValidator::endOfMatrice($op, $i + 1));
        }
        return ($i);
    }

    static function checkSemanticOp($op, $var, $fun)
    {
        $operator = ["+", "-", "%", "/", "*", "(", ")"];
        for ($i = 0; $i < strlen($op); $i++)
        {
            $nextOp = OpValidator::strgetpos($op, $operator, $i);
            if ($nextOp === $i)
            $nextOp++;
            if ($nextOp === false)
                throw new Exception("Not a valid matrice");
            $search = substr($op, $i, $nextOp - $i);
            if (!OpValidator::isValidNumber($search) && !$var->varExists($search) && !OpValidator::isMatrice($search, $var, $fun) && !OpValidator::isFun($search, $op, $i, $nextOp, $fun))
                return (0);
            $i = $nextOp;
        }
        return (1);
    }

    static function checkRightOperand($right, $var, $fun)
    {
        $right = OpValidator::replaceSpace($right, $var);
        $op = preg_replace("/\s/", "", $right);
        return (OpValidator::checkSemanticOp($right, $var, $fun));
    }

    static function validOp($array, $var, $fun)
    {
        if ($fun->isFun($array[0]))
            return (1);
        return (OpValidator::checkRightOperand($array[1], $var, $fun));
    }
}
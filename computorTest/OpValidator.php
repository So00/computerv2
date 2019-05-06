<?php

class OpValidator
{

    static function isalpha($c)
    {
        if (($c >= 'a' && $c <= 'z') || ($c >= 'A' && $c <= 'Z'))
            return (1);
        return (0);
    }

    static function replaceSpace($str, $data)
    {
        $str = preg_replace("/([a-z]+)\s([a-z]+)/", "$1*$2", $str);
        $str = preg_replace("/([0-9]+\.?[0-9]*)\s?([a-z]+)/", "$1*$2", $str);
        $str = preg_replace("/(\(.*\))\s?(\(.*\))/", "$1*$2", $str);
        $str = preg_replace("/(\(.*\))\s?([a-z]+)/", "$1*$2", $str);
        $str = preg_replace("/([0-9]+)\s?(\(.*\))/", "$1*$2", $str);
        $str = preg_replace("/(\(.*\))\s?([0-9]+)/", "$1*$2", $str);
        $str = preg_replace("/([a-z]+)\s?([0-9]+\.?[0-9]*)/", "$1*$2", $str);
        $str = preg_replace("/\s/", "", $str);
        if (count($data->var))
            foreach ($data->var as $key => $actVar)
            {
                $str = preg_replace("/($key)\s?(\(.*\))/", "$1*$2", $str);
                $str = str_replace($key, "($actVar)", $str);
            }
        return ($str);
    }

    static function validBrackets($op)
    {
        $brackets = 0;
        for ($j = 0; $j < strlen($op); $j++)
        {
            if ($op[$j] === ")")
                $brackets--;
            if ($op[$j] === "(")
                $brackets++;
            if ($brackets < 0)
                throw new Exception("Brackets are close before being open");
        }
        if ($brackets)
            throw new Exception("Some backets are never closed");
    }

    static function replaceAllFun($op, $data)
    {
        for ($i = 0; $i < strlen($op); $i++)
        {
            if (OpValidator::isalpha($op[$i]))
            {
                for ($j = $i; $j < strlen($op) && OpValidator::isalpha($op[$j]); $j++);
                $name = substr($op, $i, $j - $i);
                if ($name !== "i")
                {
                    $bracketsBegin = OpValidator::strgetpos($op, $j);
                    $bracketsEnd = OpSolve::getBracketsEnd($op, $j);
                    $function = substr($op, $i, $bracketsEnd - $i);
                    if ($data->isFunValid($function))
                    {
                        $replacement = $data->replaceFun($function);
                        $op = str_replace($function, $replacement, $op);
                        $i = -1;
                    }
                }
            }
        }
        return ($op);
    }

    static function isMatrice($str,$data)
    {
        if (preg_match("/^\[(\[([^,\]]+(,[^,\]]+)*)\](;\[([^,\]]+(,[^,\]]+)*)\])*)\]$/i", $str, $test))
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
                    if (!OpValidator::checkSemanticOp($actLignValue, $data))
                        return (0);
                    $save[$key][] = $actLignValue;
                }
            }
            return ($save);
        }
        return (0);
    }

    static function isFun($name, &$op, $i, &$nextOp, $data)
    {
        if (!$data->isFunNameSet($name))
            return (0);
        $brackets = 1;
        $j = OpValidator::strgetpos($op, $i);
        if ($j === false || $op[$j] !== "(")
            return (0);
        $j++;
        for (; $j < strlen($op) && $brackets; $j++)
        {
            if ($op[$j] === ")")
                $brackets--;
            if ($op[$j] === "(")
                $brackets++;
            if ($brackets < 0)
                throw new Exception("Brackets are close before being open");
        }
        $end = $j;
        if ($brackets)
            throw new Exception("Some brackets are never closed in this : $op");
        if ($data->isFunValid(substr($op, $i, $end-$i + 1)))
        {
            $nextOp = $end;
            return (1);
        }
        return (0);
    }

    static function isValidNumber($str)
    {
        if (preg_match("/^([+-]?[0-9]+(\.[0-9]+)?)$/", $str))
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
        throw new Exception("Not a valid matrice");
    }

    static function strgetpos($op, $position)
    {
        $operator = ["+", "-", "%", "/", "*", "(", ")"];
        for ($i = $position; $i < strlen($op); $i++)
        {
            if (array_search($op[$i], $operator) !== false && $i !== 0)
                return ($i);
            if ($op[$i] === "[")
                return (OpValidator::endOfMatrice($op, $i + 1));
        }
        return ($i);
    }

    static function checkSemanticOp($op, $data)
    {
        OpValidator::validBrackets($op);
        $save = $op;
        $op = str_replace(["(", ")"], "", $op);
        for ($i = 0; $i < strlen($op); $i++)
        {
            $nextOp = OpValidator::strgetpos($op, $i);
            $search = substr($op, $i, $nextOp - $i);
            if (!OpValidator::isValidNumber($search) && !OpValidator::isMatrice($search, $data) && !OpValidator::isFun($search, $op, $i, $nextOp, $data))
                throw new Exception("$save is not valid");
            $i = $nextOp;
        }
        return (1);
    }

    static function checkRightOperand($right, $data)
    {
        $right = OpValidator::replaceSpace($right, $data);
        $right = OpValidator::replaceAllFun($right, $data);
        $right = preg_replace("/\s/", "", $right);
        return (OpValidator::checkSemanticOp($right, $data));
    }
}
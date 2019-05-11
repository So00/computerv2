<?php

class OpValidator
{

    static function isalpha($c)
    {
        if (($c >= 'a' && $c <= 'z') || ($c >= 'A' && $c <= 'Z'))
            return (1);
        return (0);
    }

    static function replaceVar($str, $name, $value)
    {
        $nameLen = strlen($name);
        for ($i = 0; $i < strlen($str); $i++)
        {
            if ($str[$i] === $name[0] && ($i === 0 || !OpValidator::isalpha($str[$i - 1])))
            {
                for ($j = 0; $j < $nameLen && $str[$i + $j] === $name[$j]; $j++);
                if ($j === $nameLen && (!isset($str[$i + $j]) || !OpValidator::isalpha($str[$i + $j])))
                {
                    if (strstr($value, "[") === false && strstr($str, "[") === false)
                        $str = substr_replace($str, "($value)", $i, $j);
                    else
                        $str = substr_replace($str, "$value", $i, $j);
                    $i = -1;
                }
            }
        }
        return ($str);
    }

    /** Resoudre le pb de (3+3)-3  CA NE MULTIPLIE PAS */
    static function replaceSpace($str, $data)
    {
        $str = preg_replace("/([a-z]+)\s+([a-z]+)/", "$1*$2", $str);
        $str = preg_replace("/([0-9]+\.?[0-9]*)\s*([a-z]+)/", "$1*$2", $str);
        $str = preg_replace("/(\(.*\))\s*(\(.*\))/", "$1*$2", $str);
        $str = preg_replace("/(\(.*\))\s*([a-z]+)/", "$1*$2", $str);
        $str = preg_replace("/([0-9]+)\s*(\(.*\))/", "$1*$2", $str);
        $str = preg_replace("/([a-z]+)\s*([0-9]+\.?[0-9]*)/", "$1*$2", $str);
        $str = preg_replace("/\s/", "", $str);
        if (count($data->var))
            foreach ($data->var as $name => $value)
            {
                if (is_array($value))
                {
                    $matrice = "[";
                    foreach ($value as $key => $act)
                        $matrice .= "[" . join(",", $act) . "]" . (isset($value[$key + 1]) ? ";" : "");
                    $matrice .= "]";
                    $value = $matrice;
                }
                $str = OpValidator::replaceVar($str, $name, $value);
            }
        while (preg_match("/(\(.*\))\s*((\*?\s*[0-9]+)|(\*\s*(-?[0-9]+)))/", $str))
        {
            $str = preg_replace_callback("/(\(.*\))\s*(\*?\s*[0-9]+|\*\s*-?[0-9]+)/",   function ($matches)
                                                                                            {
                                                                                                if ($matches[2][0] === "*")
                                                                                                    $matches[2] = substr($matches[2], 1);
                                                                                                return ($matches[2]."*".$matches[1]);
                                                                                            }, $str);
        }
        $str = preg_replace("/(-)\s?(\(.*\))/", "-1*$2", $str);
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
            {
                echo "$op\n";
                throw new Exception("Brackets are close before being open");
            }
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
                    if ($bracketsBegin === false || $bracketsEnd === false)
                        throw new Exception("Error on $name : Not a valid function");
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

    static function isMatrice($str, $data)
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
                    if (!isset($actLignValue) || $actLignValue == "")
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
        if (preg_match("/^([+-]?[0-9]+(\.[0-9]+)?)$/", $str) || $str === "i" || $str === "-i" || $str === "+i")
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
        $operator = ["+", "-", "%", "/", "*", "(", ")", "^"];
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
        $op = str_replace("*-", "*", $op);
        $op = str_replace("/-", "/", $op);
        $op = str_replace(["+-","-+"], "-", $op);
        $op = str_replace("--", "+", $op);
        for ($i = 0; $i < strlen($op); $i++)
        {
            if ($op[$i] === "*" && $op[$i - 2] === "]" && $op[$i - 1] === "*" && $op[$i + 1] === "[")
                $i++;
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

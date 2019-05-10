<?php

include_once "OpSolve.php";

class MatriceSolve
{
    private function __construct(){}

    static function calc($matrice, $secondMatrice, $col, $line, $size, $data)
    {
        $op = "";
        for ($i = 0; $i < $size; $i++)
            $op .= "({$matrice[$line][$i]})*({$secondMatrice[$i][$col]})" . ($i < $size - 1 ? "+" : "");
        echo "$op\n";
        return (OpSolve::solve($op, $data));
    }

    /** [[1,2];[1,2];[1,2];[1,2]]**[[2,4,5];[2,3,4]]=? */
    static function multMatr($matrice, $secondMatrice, $data)
    {
        if (count($matrice[0]) === count($secondMatrice))
        {
            $newMatrice = [];
            $size = count($matrice[0]);
            $line = count($secondMatrice[0]);
            $col = count($matrice);
            for ($i = 0; $i < $col; $i++)
            {
                for ($j = 0; $j < $line; $j++)
                {
                    $newMatrice[$j][$i] = matriceSolve::calc($matrice, $secondMatrice, $j, $i, $size, $data);
                }
            }
            return ($newMatrice);
        }
        throw new Exception("First matrice has ". count($matrice). " lines and second matrice has " . count($secondMatrice[0]) . " columns");
    }

    static function multClassic($mult, $matrice, $data)
    {
        $mult = floatval($mult);
        foreach ($matrice as $line => $actLine)
            foreach ($actLine as $column => $actColumn)
                $matrice[$line][$column] = OpSolve::solve($mult."*($actColumn)", $data);
        return ($matrice);
    }

    static function checkMatrOpValidity($op)
    {
        $depth = 0;
        for ($i = 0; $i < strlen($op); $i++)
        {
            if ($op[$i] === "[")
                $depth++;
            if ($op[$i] === "]")
                $depth--;
            if (!$depth && $op[$i] === "-")
            {
                if ($i > 0 && $op[$i - 1] === "*")
                    $i++;
                else if ($i === 0)
                {
                    $i++;
                    while (is_numeric($op[$i]) || $op[$i] === "i")
                        $i++;
                }
            }
            if (!$depth && $op[$i] !== "*" && $op[$i] !== "i" && !is_numeric($op[$i]) && $op[$i] !== "]")
                throw new Exception("Not a valid operation on matrice");
        }
    }

    static function matriceSolve($op, $data)
    {
        matriceSolve::checkMatrOpValidity($op);
        $beginMatrice = strpos($op, "[");
        $endMatrice = OpValidator::endOfMatrice($op, $beginMatrice + 1);
        $matrice = OpValidator::isMatrice(substr($op, $beginMatrice, $endMatrice - $beginMatrice), $data);
        $op = substr_replace($op, "[]", $beginMatrice, $endMatrice - $beginMatrice);
        $endMatrice = $beginMatrice + 1;
        while ($op[$beginMatrice - 1] === "*")
        {
            $lastNum = OpSolve::getLastNb($op, $beginMatrice - 1);
            if ($op[$lastNum - 1] === "-")
                $lastNum--;
            $number = substr($op, $lastNum, $beginMatrice - $lastNum);
            $matrice = MatriceSolve::multClassic($number, $matrice, $data);
            $op = substr_replace($op, "", $lastNum, strlen($number));
            $beginMatrice = strpos($op, "[");
        }
        $endMatrice = $beginMatrice + 1;
        while ($op[$endMatrice + 1] === "*")
        {
            if ($op[$endMatrice + 2] !== "*")
            {
                $nextNum = OpSolve::getNextnb($op, $endMatrice + 1);
                $number = substr($op, $endMatrice + 1, $nextNum - $endMatrice);
                $number = substr_replace($number, "", 0, 1);
                $matrice = MatriceSolve::multClassic($number, $matrice, $data);
                $op = substr_replace($op, "", $endMatrice, $nextNum - $endMatrice);
                $beginMatrice = strpos($op, "[");
            }
            else
            {
                $endSecondMatrice = OpValidator::endOfMatrice($op, $endMatrice + 4);
                $beginSecondMatrice = $endMatrice + 3;
                $secondMatrice = OpValidator::isMatrice(substr($op, $beginSecondMatrice, $endSecondMatrice - $beginSecondMatrice), $data);
                $matrice = matriceSolve::multMatr($matrice, $secondMatrice, $data);
                var_dump($matrice);
                die();
            }
        }
        return ($matrice);
    }
}

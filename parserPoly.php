<?php

include_once "SolvePoly.php";

class       ParserPoly extends SolvePoly
{
        public $array;

        function __construct()
        {

        }

        function parse()
        {
            $this->array["left"] = $this->transformParsIntoPol($this->array["left"]);
            $this->array["right"] = $this->transformParsIntoPol($this->array["right"]);
            $this->mergeToZero();
        }

        function mergeToZero()
        {
            $this->array["left"]["pow0"] -= $this->array["right"]["pow0"];
            $this->array["right"]["pow0"] = 0;
            $this->array["left"]["pow1"] -= $this->array["right"]["pow1"];
            $this->array["right"]["pow1"] = 0;
            $this->array["left"]["pow2"] -= $this->array["right"]["pow2"];
            $this->array["right"]["pow2"] = 0;
            if ($this->array["left"]["pow2"] === 0 && $this->array["left"]["pow1"] === 0)
            {
                if ($this->array["left"]["pow0"] == 0)
                    throw new Exception("All real numbers are solutions");
                throw new Exception("No solution possible");
            }
        }

        function    transformParsIntoPol($array)
        {
            $ret = ["pow0" => 0, "pow1" => 0, "pow2" => 0];
            foreach ($array as $actArray)
            {
                if (($pos = stripos($actArray, "x")) === FALSE)
                {
                    $ret["pow0"] += intval($actArray);
                }
                else
                {
                    while ($pos < strlen($actArray) && (is_numeric($actArray[++$pos]) === FALSE && $actArray[$pos] !== '-' && $actArray[$pos] !== '+'));
                    if ($pos < strlen($actArray))
                        $tmpXpow = intval(substr($actArray, $pos));
                    else
                        $tmpXpow = 1;
                    if ($actArray[0] == '+' || $actArray[0] == '-' || is_numeric($actArray[0]))
                    {
                        if (!is_numeric($actArray[0]) && !is_numeric($actArray[1]))
                        {
                            $actArray = str_replace(" ", "", $actArray);
                            if ($actArray[1] == "x" || $actArray[1] == "X")
                                $actArray = str_replace(["x", "X"], "1x", $actArray);
                        }
                        $mult = intval($actArray);
                    }
                    else
                        $mult = 1;
                    if ($tmpXpow > 2 || $tmpXpow < 0)
                        throw new Exception("$tmpXpow is not valid for a second degres polynom");
                    $ret["pow$tmpXpow"] += $mult;
                }
            }
            return ($ret);
        }

        function    validPol($array)
        {
            $x = false;
            foreach ($array as $actSide)
            {
                if ($x === false)
                    $x = stripos($actSide, 'x');
                if (preg_match("/^((?![^\d^\+^\*^\%^\-^\=^\/^\^^x]).)*$/i", $actSide) === 0)
                    return (0);
                preg_match_all("#((?:((?:[+-]?(\d+)?\*?x(\^[+-]?\d+)?)|(?:[+-]?\d+))))#i", $actSide, $tmpAr);
                $ret[] = $tmpAr[0];
            }
            if ($x === false)
                return (0);
            $this->array["left"] = $ret[0];
            $this->array["right"] = $ret[1];
            $this->parse();
            return ($ret);
        }
}

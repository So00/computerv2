<?php

class       PolynomParse
{
        public $options;
        public $array;

        function __construct($left, $right)
        {
            $this->parse($left, $right);
        }

        function parse($left, $right)
        {
            $this->array["left"] = $this->parsePol($left);
            $this->array["right"] = $this->parsePol($right);
            $this->array["left"] = $this->transformParsIntoPol($this->array["left"]);
            $this->array["right"] = $this->transformParsIntoPol($this->array["right"]);
            $maxDeg = 0;
            if (!empty($this->options["step"]))
            {
                echo "Detailled : \n";
                $firstOnly = 1;
                foreach ($this->array as $actArray)
                {
                    $start = 1;
                    foreach ($actArray as $pow => $nb)
                    {
                        $pow = intval(substr($pow, 3));
                        if ($pow > $maxDeg && $nb != 0)
                            $maxDeg = $pow;
                        echo (!$start ? ($nb >= 0 ? "+ " : "- ") : "") . ($nb < 0 ? -$nb : $nb) .($pow != 0 ? " * X". ($pow == 2 ? "^$pow": "") : "") . " ";
                        $start = 0;
                    }
                    if ($firstOnly)
                        echo "= ";
                    $firstOnly = 0;
                }
                echo "\n";
            }
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

        function    getOp()
        {
            return ($this->array);
        }

        function    transformParsIntoPol($array)
        {
            $ret = ["pow2" => 0, "pow1" => 0, "pow0" => 0];
            foreach ($array as $actArray)
            {
                if (($xPos = stripos($actArray, "x")) === FALSE)
                {
                    $ret["pow0"] += floatval($actArray);
                }
                else
                {
                    if ($xPos > 1 && $actArray[$xPos - 1] == "*")
                    {
                        $actArray = str_replace(substr($actArray, 0, $xPos), substr($actArray, 0, $xPos - 1), $actArray);
                        $xPos -= 1;
                    }
                    if ($xPos == 1 && ($actArray[$xPos - 1] === "+" || $actArray[$xPos - 1] === "-"))
                    {
                        $actArray = str_replace(substr($actArray, 0, $xPos), substr($actArray, 0, $xPos) . "1", $actArray);
                        $xPos += 1;
                    }
                    if ($xPos !== 0 && !is_numeric(substr($actArray, 0, $xPos)))
                        throw new Exception("Non numeric value on " . $actArray);
                    if ($xPos === 0)
                        $mult = 1;
                    else
                        $mult = floatval(substr($actArray, 0, $xPos));
                    $powPos = stripos($actArray, "^");
                    if ($powPos && strcasecmp($actArray[$powPos - 1],"x"))
                        throw new Exception("You must have x before your power sign ^");
                    if ($powPos !== false)
                    {
                        if ($powPos === strlen($actArray) - 1)
                            throw new Exception("Put a numeric value after ^, please");
                        if (!is_numeric(substr($actArray, $powPos + 1 - strlen($actArray))))
                            throw new Exception(substr($actArray, $powPos + 1 - strlen($actArray)) . " is not a numeric value");
                        $tmpXpow = floatval(substr($actArray, $powPos + 1 - strlen($actArray)));
                    }
                    else
                        $tmpXpow = 1;
                    if ($tmpXpow > 2 || $tmpXpow < 0 || intval($tmpXpow) != $tmpXpow)
                        throw new Exception("$tmpXpow is not valid for a second degres polynom");
                    $ret["pow$tmpXpow"] += $mult;
                }
            }
            return ($ret);
        }

        function    parsePol($pol)
        {
            if (preg_match("/[a-wy-zA-WY-Z,]/", $pol) || preg_match("/([^\d\/\*\.\^x\+-])/i", $pol))
                throw new Exception("Not a valid polynom");
            preg_match_all("/(([+\-*]*([+\-*]*(\.?\d+(\.[\d\.]+)?)?\*?x\^?(\.?\d+(\.[\d\.]+)?)?)|([+\-*]*\.?\d+(\.[\d\.]+)?)))/i", $pol, $array);
            return ($array[0]);
        }

        function reducedForm ()
        {
            $start = 1;
            echo "Reduced form is : \n";
            $maxDeg = 0;
            foreach ($this->array["left"] as $pow => $nb)
            {
                $pow = intval(substr($pow, 3));
                if ($pow > $maxDeg && $nb != 0)
                    $maxDeg = $pow;
                if ($nb != 0)
                {
                    echo ($nb < 0 ? "- " : ($start ?  "" : "+ ")) . ($nb < 0 ? -$nb : $nb) .($pow != 0 ? " * X". ($pow == 2 ? "^$pow": "") : "") . " ";
                    $start = 0;
                }
            }
            echo "= 0\n";
            echo "Max degree is $maxDeg \n";
        }
}

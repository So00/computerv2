<?php

include_once "OpValidator.php";

class ControlerFun
{
    public  $fun;
    public  $save;
    public  $solve;

    function __construct()
    {
        $var = array();
    }

    function valid_op($string)
    {
        $operator = ["+", "-", "%", "/", "*", "^", "(", ")"];
        if (!OpValidator::validBrackets($string))
            return (0);
        for ($i = 0; $i < strlen($string); $i++)
        {
            if (OpValidator::isalpha($string[$i]))
                while (OpValidator::isalpha($string[++$i]));
            else if (OpValidator::isdigit($string[$i]))
            {
                while(OpValidator::isdigit($string[++$i]));
                if ($string[$i] === ".")
                    while(OpValidator::isdigit($string[++$i]));
            }
            if (array_search($string[$i], $operator) === false && $i !== strlen($string))
                return (0);
        }
        return (1);
    }

    function isFun($str)
    {
        if (preg_match("/^(([a-z]+)(\(([a-z]+)(?:(,[a-z]+)*)\))?)$/im", $str, $ret) && !empty($ret[3]) && !empty($ret[2]))
            return ($ret);
        return (NULL);
    }

    function funExist($str)
    {
        // if (preg_match("/^(([a-z]+)(\(([a-z]+)(?:(,[a-z]+)*)\))?)$/im", $str, $ret) && !empty($ret[3]) && !empty($ret[2]))
        //     if ()
    }

    function isValidFun($array)
    {
        $tmp = preg_replace("/\s/", "", $array);
        if (($ret = $this->isFun($tmp[0])) !== NULL)
        {
            $tmpName=$ret[2];
            $tmpVarName = explode(",", substr($ret[3], 1, strlen($ret[3]) - 2));
            foreach ($tmpVarName as $actVarName)
                if (strstr($array[1], $actVarName) === false)
                    return (0);
            preg_match_all("/([a-z]+)/i", $array[1], $allWord);
            foreach ($allWord[0] as $actWord)
            {
                if (array_search($actWord, $tmpVarName) === false && $actWord !== "i")
                    return (0);
            }
            $array[1] = OpValidator::replaceSpace($array[1]);
            $array[1] = preg_replace("/\s/", "", $array[1]);
            if ($this->valid_op($array[1]))
            {
                $this->save["name"] = $tmpName;
                $this->save["param"] = $tmpVarName;
                $this->save["op"] = $array[1];
                return (1);
            }
        }
        return(0);
    }

    function save()
    {
        $this->fun[$this->save["name"]]["op"] = $this->save["op"];
        $this->fun[$this->save["name"]]["param"] = $this->save["param"];
        // echo $this->save["name"] . "(" . join(",", $this->save["param"]) . ") = " . $this->save["op"]. "\n";
        echo "Function saved\n";
        unset($this->save);
    }

    function list()
    {
        if (!empty($this->fun))
            foreach ($this->fun as $key => $value)
                echo "$key(".  join(",", $value["param"]) . ") = {$value["op"]}\n";
        else
            echo "No function saved\n";
    }

    function validParam($name, $var)
    {
        foreach ($var as $key => $actVar)
        {
            $actVar = preg_replace("/[0-9]/", "", $actVar);
            if (!empty($actVar) && isset($varObj->var[$actVar]) === false && $actVar !== "i")
                throw new Exception("$actVar is not a valid variable");
            else if (!empty($actVar) && $actVar !== "i")
                $var[$key] = $varObj->var[$actVar];
        }
        if (count($var) !== count($this->fun[$name]["param"]))
            throw new Exception((count($this->fun[$name]["param"])) . " parameters excepted, " . (count($var)) . " given");
    }

    function validSolveFun($array, $varObj)
    {
        $array[0] = preg_replace("/\s/", "", $array[0]);
        if (preg_match("/^(([a-z]+)(\(([0-9a-z]+(\.[0-9]+)?)(?:(,[0-9a-z]+(\.[0-9]+)?)*)\))?)$/im", $array[0], $ret) && !empty($ret[3]) && !empty($ret[2]))
        {
            $name = $ret[2];
            $var = explode(",", substr($ret[3], 1, strlen($ret[3]) - 2));
            if (isset($this->fun[$name]) === false)
                throw new Exception("Not a valid function name");
            $this->validParam($name, $var);
            return (str_replace($this->fun[$name]["param"], $var, $this->fun[$name]["op"]));
        }
        return (NULL);
    }
}
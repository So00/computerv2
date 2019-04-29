<?php

include_once "OpValidator.php";

class ControlerFun
{
    public  $fun;
    public  $save;
    public  $solve;
    public  $var;

    function __construct()
    {
        $param = array();
    }

    /** Can't use the valid op from validator because unknown value like x, y ... are not set in var object */
    function validOpFun($string)
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

    /** Is the string a function? */
    function isFun($str)
    {
        $str = preg_replace("/\s/", "", $str);
        if (preg_match("/^(([a-z]+)\((([a-z]+)(,?([a-z]+))*)\))?$/im", $str, $ret) && !empty($ret[2]) && !empty($ret[3]))
            return ($ret);
        return (NULL);
    }

    /** Is the name set? */
    function issetFunName($name)
    {
        return (isset($this->fun[trim($name)]));
    }

    /** Does the function exists (name + param) */
    function funExist($str)
    {
        if (preg_match("/^(([a-z]+)(\([a-z0-9]+((,[a-z0-9]+)*))\))$/im", $str, $ret) && !empty($ret[3]) && !empty($ret[2]))
            if (isset($this->fun[$ret[2]]) && count($this->fun[$ret[2]]["param"]) === count(explode(",", $ret[3])))
                return (1);
        return(0);
    }

    /** Is the total (left + right) a valid function? */
    function isValidFun($operand)
    {
        if (($ret = $this->isFun($operand[0])) !== NULL)
        {
            $funName = $ret[2];
            if ($this->var->varExists($funName))
                throw new Exception("The name is already used in function");
            $tmpVarName = explode(",", $ret[3]);
            foreach ($tmpVarName as $actVarName)
                if (strstr($operand[1], $actVarName) === false)
                    return (0);
            preg_match_all("/([a-z]+)/i", $operand[1], $allWord);
            foreach ($allWord[0] as $actWord)
            {
                if (array_search($actWord, $tmpVarName) === false && $actWord !== "i")
                    return (0);
            }
            $operand[1] = OpValidator::replaceSpace($operand[1], $this->var);
            $operand[1] = preg_replace("/\s/", "", $operand[1]);
            if ($this->validOpFun($operand[1]))
            {
                $this->save["name"] = $funName;
                $this->save["param"] = $tmpVarName;
                $this->save["op"] = $operand[1];
                return (1);
            }
        }
        return(0);
    }

    /** Saving a function in the array */
    function save()
    {
        $this->fun[$this->save["name"]]["op"] = $this->save["op"];
        $this->fun[$this->save["name"]]["param"] = $this->save["param"];
        echo "Function saved\n";
        unset($this->save);
    }

    /** List all existing function */
    function list()
    {
        if (!empty($this->fun))
            foreach ($this->fun as $key => $value)
                echo "$key(".  join(",", $value["param"]) . ") = {$value["op"]}\n";
        else
            echo "No function saved\n";
    }

    /** Check if there is the good number of param */
    function validParam($name, &$param)
    {
        foreach ($param as $key => $actVar)
        {
            $actVar = preg_replace("/[0-9\s]/", "", $actVar);
            if (!empty($actVar) && $this->var->varExists($actVar) === false && $actVar !== "i")
                throw new Exception("$actVar is not a valid variable");
            else if (!empty($actVar) && $actVar !== "i")
            {
                $param[$key] = OpValidator::replaceSpace($param[$key], $this->var);
                $param[$key] = str_replace($actVar ,$this->var->var[$actVar], $param[$key]);
            }
        }
        if (count($param) !== count($this->fun[$name]["param"]))
            throw new Exception((count($this->fun[$name]["param"])) . " parameters excepted, " . (count($param)) . " given");
    }

    /** Does operand are valid for solving that operation? Do it all in opValidator i guess for all operation? */
    function validSolveFun($operand, $paramObj)
    {
        $operand[0] = preg_replace("/\s/", "", $operand[0]);
        if (preg_match("/^(([a-z]+)(\(([0-9a-z]+(\.[0-9]+)?)(?:(,[0-9a-z]+(\.[0-9]+)?)*)\))?)$/im", $operand[0], $ret) && !empty($ret[3]) && !empty($ret[2]))
        {
            $name = $ret[2];
            if ($this->var->varExists($name))
                return (0);
            if (isset($this->fun[$name]) === false)
                throw new Exception("Not a valid function name");
            $param = explode(",", substr($ret[3], 1, strlen($ret[3]) - 2));
            $this->validParam($name, $param);
            return (str_replace($this->fun[$name]["param"], $param, $this->fun[$name]["op"]));
        }
        return (NULL);
    }
}
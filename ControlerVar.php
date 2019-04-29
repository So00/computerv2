<?php

include_once "OpValidator.php";

class ControlerVar
{
    public  $var;
    public  $fun;

    function __construct()
    {
        $var = array();
    }

    function varExists($str)
    {
        return (isset($this->var[$str]));
    }

    function validVarName($str)
    {
        $str = trim($str);
        if (preg_match("/[^a-z]/i", $str) === 0 && $str !== "i")
            return (1);
        return (0);
    }

    function trySave($array, $var, $fun)
    {
        if (!$this->validVarName($array[0]))
            return (0);
        if ($fun->issetFunName($array[0]))
            throw new Exception("The name is already used in function, be more specific");
        if (OpValidator::checkRightOperand($array[1],$this->var, $this->fun) === false)
            return (0);
        if (!empty($this->var[$array[1]]))
            $this->var[$array[0]] = $this->var[$array[1]];
        else
            $this->var[$array[0]] = OpValidator::replaceSpace($array[1], $this);
        echo "Variable saved\n";
    }

    function list()
    {
        if (!empty($this->var))
            foreach ($this->var as $key => $value)
                echo "$key = $value\n";
        else
            echo "No variable saved\n";
    }
}
<?php

include_once "OpValidator.php";

class ControlerVar
{
    public  $var;

    function __construct()
    {
        $var = array();
    }

    function varExists($str)
    {
        if (isset($this->var[$str]))
            return (1);
        return (0);
    }

    function validVarName($str)
    {
        if (preg_match("/[^a-z\s]/i", $str) === 0 && preg_replace("\s", "", $str) !== "i")
            return (1);
        return (0);
    }

    function trySave($array)
    {
        // $array = preg_replace("/\s/", "", $array);
        // if ( && (preg_match("/^(i?[+-]?([0-9]*)(\.([0-9]+))?i?)$/", $array[1]) || !empty($this->var[$array[1]])))
        //     return (1);
        // return(0);
    }

    function save($array)
    {
        $array = preg_replace("/\s/", "", $array);
        if (!empty($this->var[$array[1]]))
            $this->var[$array[0]] = $this->var[$array[1]];
        else
            $this->var[$array[0]] = OpValidator::replaceSpace($array[1]);
        // echo $this->var[$array[0]] . "\n";
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
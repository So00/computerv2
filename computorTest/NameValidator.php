<?php

class NameValidator
{
    static function validFunName($fun, $data)
    {
        $fun = preg_replace("/\s/", "", $fun);
        if (preg_match("/^(([a-z]+)\((([a-z]+)(,?([a-z]+))*)\))?$/im", $fun, $ret) && !empty($ret[2]) && !empty($ret[3]))
        {
            if ($data->isVarNameSet($ret[2]))
                throw new Exception("The name {$ret[2]} is already a value name");
            return (1);
        }
        return (0);
    }

    static function validVarName($name, $data)
    {
        $name = trim($name);
        if (preg_match("/[^a-z]/i", $name) === 0 && $name !== "i")
        {
            if ($data->isFunNameSet($name))
                throw new Exception("The name $name is already a function name");
            return (1);
        }
        return (0);   
    }
}
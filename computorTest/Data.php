<?php

class Data
{
    /** Saving variable here */
    public $var;
    /** Saving function here */
    public $fun;
    
    function __construct()
    {
        $this->var = array();
        $this->fun = array();
    }

    function isVarNameSet($name)
    {
        return (isset($this->var[$name]));
    }
    
    function isFunNameSet($name)
    {
        return (isset($this->fun[$name]));
    }

    function replaceFun($function)
    {
        preg_match("/(([a-z]+)\((([^,]*)(,.*[^,])*))\)$/i", $function, $expData);
        $name = $expData[2];
        $funParam = $this->splitFunArg($expData[3]);
        $str = $this->fun[$name]["op"];
        foreach($funParam as $key => $actParam)
            $str = str_replace($this->fun[$name]["param"][$key], "($actParam)", $str);
        return ($str);
    }

    function splitFunArg($args)
    {
        $depth = 0;
        for ($i = 0; $i < strlen($args); $i++)
        {
            if ($args[$i] === "(")
                $depth++;
            if ($args[$i] === ")")
                $depth--;
            if ($args[$i] === "," && !$depth)
                $args[$i] = "!";
        }
        return (explode("!", $args));
    }

    function isFunValid($function)
    {
        if (!preg_match("/(([a-z]+)\((([^,]*)(,.*[^,])*))\)$/i", $function, $expData) || empty($expData[2]) || empty($expData[3]))
            throw new Exception("Error in this function : $function");
        $name = $expData[2];
        if (!$this->isFunNameSet($name))
            throw new Exception("$name is not a valid function name");
        $funParam = $this->splitFunArg($expData[3]);
        if (count($funParam) !== count($this->fun[$name]["param"]))
            throw new Exception("You gave " . count($funParam) . " parameters for the function $name, it has " . count($this->fun[$name]["param"]). " parameters");
        foreach($funParam as $key => $actParam)
        {
            if (!OpValidator::checkRightOperand($actParam, $this))
                throw new Exception("$actParam is not a valid parameter");
        }
        return (1);
    }

    function showMatrice($matrice, $name)
    {
        echo "Matrice $name : \n";
        foreach ($matrice as $line)
        {
            echo "  [";
            foreach ($line as $keyValue => $actValue)
            {
                echo "$actValue". ( isset($line[$keyValue + 1]) ? "," : "");
            }
            echo "]\n";
        }
    }

    function listVar()
    {
        if (!empty($this->var))
            foreach ($this->var as $key => $value)
                if (is_array($value))
                    $this->showMatrice($value, $key);
                else
                    echo "$key = $value\n";
        else
            echo "No variable saved\n";
    }

    function listFun()
    {
        if (!empty($this->fun))
            foreach ($this->fun as $key => $value)
                echo "$key(".  join(",", $value["param"]) . ") = {$value["op"]}\n";
        else
            echo "No function saved\n";
    }

    function checkFunRightOperand(&$rightOp, $funParam, $name)
    {
        preg_match_all("/([a-z]+)/i", $rightOp, $allWord);
        $tmp = $rightOp;
        foreach ($funParam as $actParam)
            if (array_search($actParam, $allWord[0]) === false)
                throw new Exception("$actParam is not used in the function");
            else if ($this->isVarNameSet($actParam))
                throw new Exception("$actParam already exists as var");
            else if ($actParam === "i")
                throw new Exception("$actParam is already used for imaginary number");
            else if ($actParam === $name)
                throw new Exception("$name is already used for the function name, you can't use it as param");
            else
                $tmp = str_replace($actParam , "1", $tmp);
        $rightOp = OpValidator::replaceSpace($rightOp, $this);
        OpValidator::checkRightOperand($tmp, $this);
        return (1);
    }

    function funcSave($leftOp, $rightOp)
    {
        if (!preg_match("/(([a-z]+)\((([a-z]+|[0-9](\.[0-9]+)?)(,([a-z]+|[0-9](\.[0-9]+)?))*)\))?$/i", $leftOp, $expData) && empty($expData[2]) && empty($expData[3]))
            throw new Exception("Error in the left operand of your function");
        $name = $expData[2];
        $funParam = explode(",", $expData[3]);
        if ($this->checkFunRightOperand($rightOp, $funParam, $name))
        {
            $this->fun[$name]["op"] = $rightOp;
            $this->fun[$name]["param"] = $funParam;
            echo "Function $name is saved (".  join(",", $this->fun[$name]["param"]) . ") = {$this->fun[$name]["op"]}\n";
        }
    }

    function varSave($leftOp, $rightOp)
    {
        if (OpValidator::checkRightOperand($rightOp, $this))
        {
            $rightOp = OpValidator::replaceSpace($rightOp, $this);
            $rightOp = OpValidator::replaceAllFun($rightOp, $this);
            $this->var[$leftOp] = OpSolve::solve($rightOp, $this);
            echo "Value $leftOp is saved : $rightOp\n";
        }
    }
}

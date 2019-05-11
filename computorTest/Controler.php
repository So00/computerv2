<?php
include_once "Data.php";
include_once "NameValidator.php";
include_once "OpValidator.php";
include_once "OpSolve.php";

class Controler
{
        public $str;
        public $array;
        public $data;

        function __construct($data)
        {
            $this->data = $data;
        }

        function parse()
        {
            $this->array = explode("=" , $this->str);
            if (count($this->array) != 2)
                throw new Exception("Usage :"
                ."\n Create a function : funcName(param1, param2) = param1 * param2"
                ."\n Create a variable : varName = value"
                ."\n Solve polynom     : x^2 + x + 1 = 2 * x^2 + 0 * x + 0");
            $this->array[0] = trim($this->array[0]);
            $this->array[1] = trim($this->array[1]);
            if (strchr($this->array[1], "?") === false)
            {
                /** Only data goes here, so fun or var */
                if (NameValidator::validFunName($this->array[0], $this->data))
                    $this->data->funcSave($this->array[0], $this->array[1]);
                else if (NameValidator::validVarName($this->array[0], $this->data))
                    $this->data->varSave($this->array[0], $this->array[1]);
                else
                    throw new Exception("The name at left operand is not good to set a variable or a function");
            }
            else
            {
                if ($this->array[1][strlen($this->array[1]) - 1] !== "?")
                    throw new Exception("Please put the \"?\" at the end");
                if ($this->array[1] === "?")
                {
                    $result = OpSolve::solve($this->array[0], $this->data);
                    if (is_array($result))
                        foreach ($result as $actRes)
                            echo "[".join(",", $actRes)."]\n";
                    else
                        echo $result."\n";
                }
                else
                    echo "other stuff\n";
            }
        }

        function    getData()
        {
            return ($this->array);
        }
}

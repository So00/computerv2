<?php
include_once "ControlerPoly.php";
include_once "ControlerVar.php";
include_once "ControlerFun.php";

class Controler
{
        public $str;
        public $array;
        public $var;
        public $poly;
        public $fun;

        function __construct()
        {
            $this->poly = new ControlerPoly();
            $this->var = new ControlerVar();
            $this->fun = new ControlerFun();
            $this->fun->var = $this->var;
            $this->var->fun = $this->fun;
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
                if (OpValidator::validOp($this->array, $this->var, $this->fun))
                {
                    if ($this->var->trySave($this->array, $this->var, $this->fun))
                        echo "Variable saved\n";
                    else if ($this->fun->isValidFun($this->array))
                        $this->fun->save();
                    else
                        echo "I didn't understand what you wrote\n";
                }
                else
                    throw new Exception("Not a valid right operand");
            }
            else
            {
                if ($this->array[1][strlen($this->array[1]) - 1] !== "?")
                    throw new Exception("Please put the \"?\" at the end");
                $this->array[1] = substr($this->array[1], 0, strlen($this->array[1]) - 1);
                //variable as param in fun to solve
                if ($this->fun->validSolveFun($this->array, $this->var))
                    $this->fun->solve();
                else if (($arr = $this->poly->validPol($this->array)))
                    $this->poly->solve($arr);
            }
        }

        function    getData()
        {
            return ($this->array);
        }
}

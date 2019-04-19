<?php
include_once "parserPoly.php";

class Parser
{  
        public $str;
        public $var;
        public $array;
        public $poly;

        function __construct()
        {
            $this->poly = new ParserPoly();
        }

        function parse()
        {
            $this->str = str_replace(" ", "", $this->str);
            $this->array = explode("=" , $this->str);
            if (count($this->array) != 2)
                throw new Exception("Not a valid operation");
            if (($arr = $this->poly->validPol($this->array)))
                $this->poly->solve($arr);
        }

        function    getData()
        {
            return ($this->array);
        }
}

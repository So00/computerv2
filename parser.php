<?php

class       Parser
{  
        public $str;
        public $options;
        public $array;

        function __construct(){}

        function parse()
        {
            $this->str = str_replace(" ", "", $this->str);
            $split = explode("=" , $this->str);
        }

        function    getData()
        {
            return ($this->array);
        }
}

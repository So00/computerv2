<?php

class OpValidator
{
    private function construct() {}

    static function isalpha($c)
    {
        if (($c >= 'a' && $c <= 'z') || ($c >= 'A' && $c <= 'Z'))
            return (1);
        return (0);
    }
    
    static function isdigit($c)
    {
        if ($c >= '0' && $c <= '9')
            return (1);
        return (0);
    }

    static function replaceSpace($str)
    {
        $str = preg_replace("/([a-z]+)\s([a-z]+)/", "$1*$2", $str);
        $str = preg_replace("/([0-9]+\.?[0-9]*)\s?([a-z]+)/", "$1*$2", $str);
        $str = preg_replace("/([a-z]+)\s?([0-9]+\.?[0-9]*)/", "$1*$2", $str);
        $str = preg_replace("/(\(.*\))\s?(\(.*\))/", "$1*$2", $str);
        $str = preg_replace("/(\(.*\))\s?([a-z]+)/", "$1*$2", $str);
        $str = preg_replace("/([a-z]+)\s?(\(.*\))/", "$1*$2", $str);
        $str = preg_replace("/([0-9]+)\s?(\(.*\))/", "$1*$2", $str);
        $str = preg_replace("/(\(.*\))\s?([0-9]+)/", "$1*$2", $str);
        return ($str);
    }

    static function validBrackets($string)
    {
        while (($opBracket = strpos($string, "(", 0)) !== false && ($clBrackets = strpos($string, ")", 0)) !== false)
        {
            if ($clBrackets < $opBracket)
                return (0);
            $string[$opBracket] = " ";
            $string[$clBrackets] = " ";
        }
        $opBracket = strpos($string, "(", 0);
        $clBrackets = strpos($string, ")", 0);
        if (($opBracket && $clBrackets === false) || ($opBracket === false && $clBrackets))
            return (0);
        return (1);
    }

    static function isMatrice($str)
    {

    }

    static function isVar($str)
    {

    }

    static function isValidNumber($str)
    {

    }

    static function validOp($array, $var, $fun)
    {
        $array[1] = replaceSpace($array[1]);
        for ($i = 0; $i < strlen($array[1]); $i++)
        {
            
        }
    }
}
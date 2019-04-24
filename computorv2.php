<?php
    include_once "./controler.php";

    $continue = 1;
    $controler = new Controler();

    while ($continue)
    {
        $str = readline("> ");
        if (strcasecmp($str, "exit") === 0)
            $continue = 0;
        else
            try
            {
                if (strcasecmp($str, "listvar") === 0)
                    $controler->var->list();
                else if (strcasecmp($str, "listfun") === 0)
                    $controler->fun->list();
                else
                {
                    $controler->str = $str;
                    $controler->parse();
                }
            }
            catch (Exception $e)
            {
                echo $e->getMessage() . "\n";
            }
    }
    echo "Thanks for using my computerV2\n";

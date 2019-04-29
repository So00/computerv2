<?php
    include_once "./controler.php";

    $continue = 1;
    $data = new Data();
    $controler = new Controler($data);

    while ($continue)
    {
        $str = readline("> ");
        if (strcasecmp($str, "exit") === 0)
            $continue = 0;
        else
            try
            {
                if (strcasecmp($str, "listvar") === 0)
                    $data->listVar();
                else if (strcasecmp($str, "listfun") === 0)
                    $data->listFun();
                else
                {
                    $controler->str = strtolower($str);
                    $controler->parse();
                }
            }
            catch (Exception $e)
            {
                echo $e->getMessage() . "\n";
            }
    }
    echo "Thanks for using my computerV2\n";

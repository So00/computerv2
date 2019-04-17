<?php
    include_once "./parser.php";
    include_once "./solve.php";

    $continue = 1;
    $parser = new Parser();

    while ($continue)
    {
        $str = readline("");
        echo $str."\n";
        if (strcasecmp($str, "exit") === 0)
            $continue = 0;
    }
    echo "Thanks for using my computerV2\n";

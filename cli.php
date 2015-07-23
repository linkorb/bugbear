<?php

require __DIR__ . "/vendor/autoload.php";

$cli = new crodas\Cli\cli;
$cli->addDirectory(__DIR__ . '/src');
$cli->main();

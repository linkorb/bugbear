<?php

namespace BugBear;

/**
 *  @Cli("run")
 *  @Arg("script", REQUIRED)
 */
function main($input, $output)
{
    $app = new BugBear($input->getArgument('script'));
    $app->run($output);
}

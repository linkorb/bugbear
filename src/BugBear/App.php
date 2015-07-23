<?php

namespace BugBear;

/**
 *  @Cli("bugbear")
 *  @Arg("script", REQUIRED)
 */
function main($input, $output)
{
    $app = new BugBear($input->getArgument('script'));
    $app->run($output);
}

<?php

namespace BugBear;

use Symfony\Component\Yaml\Dumper;

/**
 *  @Cli("run")
 *  @Arg("script", REQUIRED)
 *  @Option("through", VALUE_REQUIRED)
 */
function main($input, $output)
{
    $app = new BugBear($input->getArgument('script'), $input->getOption('through'));
    $app->run($output);
}

/**
 *  @Cli("import")
 *  @Arg("selectors", REQUIRED)
 *  @Arg("url", IS_ARRAY)
 */
function import($input, $output)
{
    $tests = array();
    $sel   = explode(",", $input->getArgument('selectors'));
    foreach ($input->getArgument('url') as $surl) {
        try {
            $url     = new URL($surl);
            $tests[] = $url->import($sel, $output);
        } catch (\Exception $e) {
            $output->writeLn("<error> cannot fetch {$surl}</error>");
        }
    }
    
    $yaml = new Dumper;
    file_put_contents('tests.yml', $yaml->dump(compact('tests'), 10));
    $output->writeLn("<info>Wrote tests.yml</info>");
}

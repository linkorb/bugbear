<?php

namespace BugBear\Loader;

use BugBear\BugBear;
use Symfony\Component\Yaml\Parser;
use RuntimeException;

class YAML
{
    public function __construct($content, BugBear $app)
    {
        $yaml = new Parser();
        $array = $yaml->parse($content);
        if (empty($array['tests'])) {
            throw new RuntimeException("There is no test defined");
        }
        foreach ($array['tests'] as $test) {
            $app->addTest($test);
        }
    }
}

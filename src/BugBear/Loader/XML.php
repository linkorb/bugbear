<?php

namespace BugBear\Loader;

use BugBear\BugBear;
use RuntimeException;

class XML
{
    public function __construct($content, BugBear $app)
    {
        $xml = simplexml_load_string($content);
        if (empty($xml->test)) {
            throw new RuntimeException("There is no test defined");
        }

        foreach ($xml->test as $test) {
            $testArray = array();

            foreach ($test as $key => $value) {
                $testArray[$key] = (string)$value;
            }

            $testArray['assert'] = array();
            foreach ($test->assert as $assert) {
                $type = (string)$assert->attributes()['type'];
                $testArray['assert'][] = array($type => (string)$assert);
            }

            $app->addTest($testArray);
        }
    }
}

<?php

namespace BugBear;

use RuntimeException;

class BugBear
{
    protected $proxy;

    public function __construct($file, $proxy = null)
    {
        $this->proxy = $proxy;
        $parts = explode(".", basename($file));
        $data  = file_get_contents($file);
        $loader = __NAMESPACE__ . '\Loader\\' . strtoupper(end($parts));
        new $loader($data, $this);
    }

    public function run($output = null)
    {
        foreach ($this->tests as $test) {
            try {
                $test->run($output);
            } catch (\Exception $e) {
                $e->url = $test->getUrl();
                throw $e;
            }
        }
    }

    public function addTest(Array $test)
    {
        if (empty($test['open'])) {
            throw new RuntimeException("There is no `open` argument");
        }
        $url = new URL($test['open'], $this->proxy);
        if (!empty($test['assert'])) {
            $url->addAssertions($test['assert']);
        }
        $this->tests[] = $url;
    }
}

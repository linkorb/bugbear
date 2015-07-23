<?php

namespace BugBear;

use GuzzleHttp;
use BugBear\Assert\Content;
use RuntimeException;

class URL
{
    protected $url;
    protected $output;
    protected $tests = array();

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function getURL()
    {
        return $this->url;
    }

    protected function log($text)
    {
        if (!$this->output) return;
        $this->output->writeLn($text);
    }

    public function run($output = null)
    {
        $this->output = $output;
        $client = new GuzzleHttp\Client;
        $response = $client->get($this->url, ['allow_redirects' => false]);
        
        $this->log("<question>" . $this->url . " </question>");
        foreach ($this->tests as $test) {
            if (!$test->test($response)) {
                throw new RuntimeException("Test " . get_class($test) . " failed");
            }
            $this->log("\t<info>Passed " . get_class($test) . "</info>");
        }
    }

    public function addAssertions(Array $assertions)
    {
        $content = array();
        foreach ($assertions as $assert) {
            $name  = __NAMESPACE__ . '\Assert\\' . strtolower(key($assert));
            $value = current($assert);
            if (!class_exists($name)) {
                $content[] = $assert;
                continue;
            }
            $this->tests[] = new $name($value);
        }
        if (!empty($content)) {
            $this->tests[] = new Content($content);
        }
    }
}

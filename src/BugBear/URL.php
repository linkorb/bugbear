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

    public function import(Array $data, $output)
    {
        $this->output = $output;
        $this->log("<question>" . $this->url . " </question>");

        $client     = new GuzzleHttp\Client;
        $response   = $client->get($this->url, ['allow_redirects' => false]);
        $statusCode = (string)$response->getStatusCode();

        if ($statusCode[0] == "3") {
            $asserts = array(
                array('statusCode' => $statusCode),
                array('redirectTo' => preg_quote(current($response->getHeader('Location')) ?: '')),
            );
        } else {
            $assert = new Assert\Content($data);
            $asserts =  array_merge($assert->import($response), [
                array('statusCode' => $response->getStatusCode()),
            ]);
        }

        return array(
            'open' => $this->url,
            'assert' => $asserts,
        );
    }

    public function run($output = null)
    {
        $this->output = $output;
        $this->log("<question>" . $this->url . " </question>");

        $client = new GuzzleHttp\Client;
        $response = $client->get($this->url, ['allow_redirects' => false]);
        
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

<?php

namespace BugBear;

use GuzzleHttp;
use BugBear\Assert\Content;
use RuntimeException;

class URL
{
    protected $url;
    protected $proxy;
    protected $options;
    protected $output;
    protected $tests = array();

    public function __construct($url, $proxy, $options = array())
    {
        $this->url   = $url;
        $this->proxy = $proxy;
        $this->options = $options;
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

        $client  = new GuzzleHttp\Client;
        
        // Default options
        $options = [
            'allow_redirects' => false,
            'http_errors' => false
        ];
        
        // Optionally add basicAuth credentials to the request
        if (isset($this->options['basicAuth'])) {
            $basicAuth = $this->options['basicAuth'];
            $authPart = explode(':', $basicAuth);
            if (count($authPart) == 1) {
                throw new RuntimeException("Auth invalid: " . $basicAuth . '. Use the format username:password');
            }
            list($username, $password) = $authPart;
            $username = $this->evaluate($username);
            $password = $this->evaluate($password);
            $options['auth'] = [
                $username,
                $password
            ];
        }
        
        $url = $this->url;
        if ($this->proxy) {
            $host = parse_url($this->url, PHP_URL_HOST);
            $url  = str_replace($host, $this->proxy, $url);
            $options['headers'] = ['Host' => $host];
        }
        $response = $client->get($url, $options);
        foreach ($this->tests as $test) {
            if (!$test->test($response)) {
                throw new RuntimeException("\t<error>✘ Failed " . get_class($test) . "</error> Expected <comment>"  . $test->getExpected() . "</comment>");
            }
            $expected = $test->getExpected();
            if (is_array($expected)) {
                $expected = "Array[" . count($expected) . "]";
            }
            $this->log("\t<info>✔ Passed " . get_class($test) . "</info> Expected <comment>" . $expected . "</comment>");
        }
    }

    private function evaluate($string)
    {
        if ($string[0]=='$') {
            $string = substr($string, 1);
            if (!getenv($string)) {
                throw new RuntimeException("Environment variable '$string' not defined");
            }
            $string = getenv($string);
        }
        return $string;
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

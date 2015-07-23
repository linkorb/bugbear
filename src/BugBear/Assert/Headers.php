<?php

namespace BugBear\Assert;

use GuzzleHttp\Psr7\Response;
use Symfony\Component\DomCrawler\Crawler;
use RuntimeException;

class Headers extends Assertion
{
    public function clean(Array $data)
    {
        $data = array_map(function($val) {
            return strtolower($val);
        }, $data);
        return array_combine($data, $data);
    }

    public function test(Response $response)
    {
        $headers  = $this->clean(array_keys($response->getHeaders()));
        $expected = $this->clean($this->expected);
        foreach ($expected as $value) {
            if (empty($headers[$value])) {
                throw new RuntimeException("Response didn't have expected header {$value}");
            }
        }

        return true;
    }
}

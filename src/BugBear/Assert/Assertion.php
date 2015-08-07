<?php

namespace BugBear\Assert;

use GuzzleHttp\Psr7\Response;

abstract class Assertion
{
    protected $expected;

    public function __construct($expected)
    {
        $this->expected = $expected;
    }
    
    public function getExpected()
    {
        return $this->expected;
    }

    abstract public function test(Response$client);
}

<?php

namespace BugBear\Assert;

use GuzzleHttp\Psr7\Response;
use RuntimeException;

class StatusCode extends Assertion
{
    public function test(Response $response)
    {
        if($response->getStatusCode() != $this->expected) {
            throw new RuntimeException(
                "✘ StatusCode assertion failed. " .
                "Expected: " . $this->expected . ' got: ' . $response->getStatusCode()
            );
        }
        return true;
    }
}

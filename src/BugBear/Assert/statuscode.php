<?php

namespace BugBear\Assert;

use GuzzleHttp\Psr7\Response;

class StatusCode extends Assertion
{
    public function test(Response $response)
    {
        return $response->getStatusCode() == $this->expected;
    }
}

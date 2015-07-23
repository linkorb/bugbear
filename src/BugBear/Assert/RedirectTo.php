<?php

namespace BugBear\Assert;

use GuzzleHttp\Psr7\Response;

class RedirectTo extends Assertion
{
    public function test(Response $response)
    {
        return preg_match("@" . $this->expected .  "@", current($response->getHeader('Location')) ?: '');
    }
}

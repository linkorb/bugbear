<?php

namespace BugBear\Assert;

use GuzzleHttp\Psr7\Response;
use Symfony\Component\DomCrawler\Crawler;
use RuntimeException;

class Content extends Assertion
{
    protected function getSelector($selector)
    {
        if (preg_match("@[\.\#]@", $selector)) {
            return $selector;
        }
        return "$selector,#{$selector},.{$selector},*[name={$selector}]";
    }

    protected function normalize($text)
    {
        return trim(preg_replace("@[ \r\t\n]+@", " ", $text));
    }

    public function test(Response $response)
    {
        $body = (string)$response->getBody();
        if (is_callable('tidy_parse_string')) {
            $tidy_config = array( 
                'clean' => true, 
                'output-xhtml' => true, 
                'wrap' => 0, 
            );
            $tidy = tidy_parse_string($body, $tidy_config, 'utf8');
            $tidy->cleanRepair(); 
            $body = (string)$tidy;
        }
        $crawler = new Crawler($body);
        foreach ($this->expected as $assert) {
            $key = key($assert);
            $val = $this->normalize(current($assert));
            $elements = $crawler->filter($this->getSelector($key));
            if ($elements->count() === 0) {
                throw new RuntimeException("Cannot find element {$key}");
            }
            $found = false;
            foreach ($elements as $element) {
                $element = new Crawler($element);
                $text    = $this->normalize($element->text() ?: $element->attr('value'));
                if (stripos($text, $val) !== false) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                throw new RuntimeException("Failed validating that {$key} contains {$val}");
            }
        }

        return true;
    }
}

<?php

use BugBear\BugBear;

class BasicTest extends phpunit_framework_testcase
{
    public static function provider()
    {
        return [
            ['test1.yml'],
            ['test1.xml'],
        ];
    }

    /**
     *  @dataProvider provider
     */
    public function testFunction($file)
    {
        $app = new BugBear(__DIR__ . "/" . $file);
        $app->run();
    }
}
